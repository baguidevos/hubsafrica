<?php

namespace App\Mcp\Tools\Applications;

use App\Enums\ApplicationEvaluationStatus;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use App\Services\ApplicationGridEvaluationService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('evaluate_application_by_grid')]
#[Title('Évaluer une candidature selon la grille du programme (sans appliquer la décision)')]
#[Description('Évalue une candidature critère par critère selon la grille d\'évaluation définie sur le programme, enregistre le brouillon d\'évaluation (notes et justifications) et calcule la recommandation indicative SANS modifier le statut final ni enrôler le candidat. La validation finale reste exclusivement humaine.')]
class EvaluateApplicationByGridTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request, ApplicationGridEvaluationService $evaluationService): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'application_id' => 'required|integer',
            'criteria_scores' => 'required|array|min:1',
            'criteria_scores.*.criterion_id' => 'required|integer',
            'criteria_scores.*.score' => 'required|numeric|min:0',
            'criteria_scores.*.comment' => 'nullable|string|max:1000',
            'summary_comment' => 'nullable|string|max:2000',
        ]);

        /** @var Application|null $application */
        $application = Application::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['application_id'])
            ->with(['program.evaluationCriteria', 'project', 'entrepreneur'])
            ->first();

        if (! $application) {
            return Response::error("Candidature #{$validated['application_id']} introuvable ou non rattachée à cette structure.");
        }

        $program = $application->program;

        if (! $program) {
            return Response::error("La candidature #{$application->id} n'est rattachée à aucun programme.");
        }

        $criteriaById = $program->evaluationCriteria->keyBy('id');

        if ($criteriaById->isEmpty()) {
            return Response::error("Aucune grille d'évaluation n'est configurée sur le programme « {$program->name} ». Configurez d'abord les critères de sélection du programme.");
        }

        $normalizedItems = [];

        foreach ($validated['criteria_scores'] as $entry) {
            $criterionId = (int) $entry['criterion_id'];
            $criterion = $criteriaById->get($criterionId);

            if (! $criterion) {
                return Response::error("Le critère #{$criterionId} n'appartient pas à la grille du programme « {$program->name} ».");
            }

            $scoreValue = (float) $entry['score'];

            if ($scoreValue > $criterion->max_score) {
                return Response::error("La note ({$scoreValue}) pour le critère « {$criterion->name} » dépasse le barème maximum ({$criterion->max_score}).");
            }

            $normalizedItems[] = [
                'criterion' => $criterion,
                'score' => $scoreValue,
                'max_score' => $criterion->max_score,
                'weight' => $criterion->weight,
                'comment' => isset($entry['comment']) ? trim((string) $entry['comment']) : null,
            ];
        }

        $weightedScore = $evaluationService->calculateWeightedScore($normalizedItems);

        if ($weightedScore === null) {
            return Response::error('Impossible de calculer le score pondéré avec les critères fournis.');
        }

        $evaluatorId = auth()->id() ?? $sae->owner_id;

        if (! $evaluatorId) {
            return Response::error('Aucun évaluateur de référence disponible sur cette structure.');
        }

        $evaluation = DB::transaction(function () use ($sae, $application, $evaluatorId, $weightedScore, $validated, $normalizedItems): ApplicationEvaluation {
            $evaluation = ApplicationEvaluation::updateOrCreate(
                [
                    'sae_id' => $sae->id,
                    'application_id' => $application->id,
                    'evaluator_id' => $evaluatorId,
                ],
                [
                    'comment' => $validated['summary_comment'] ?? null,
                    'total_score' => $weightedScore,
                    'status' => ApplicationEvaluationStatus::BROUILLON,
                    'submitted_at' => null,
                ],
            );

            foreach ($normalizedItems as $item) {
                ApplicationEvaluationScore::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'criterion_id' => $item['criterion']->id,
                    ],
                    [
                        'score' => $item['score'],
                        'comment' => $item['comment'],
                    ],
                );
            }

            // Enregistre uniquement la note indicative sans jamais changer le statut ni appliquer la décision finale
            $application->update([
                'score' => (int) round($weightedScore),
            ]);

            return $evaluation;
        });

        $admissionThreshold = $application->getAdmissionThreshold();
        $waitlistThreshold = $application->getWaitlistThreshold();
        $recommendation = $evaluationService->recommendOutcome($weightedScore, $admissionThreshold, $waitlistThreshold);

        $this->logAudit(
            action: 'EVALUATE_BY_GRID',
            entity: 'Application',
            entityId: (string) $application->id,
            details: [
                'evaluation_id' => $evaluation->id,
                'weighted_score' => $weightedScore,
                'recommendation' => $recommendation['code'],
                'decision_applied' => false,
                'summary_comment' => $validated['summary_comment'] ?? null,
            ],
        );

        return Response::structured([
            'application_id' => $application->id,
            'evaluation_id' => $evaluation->id,
            'evaluation_status' => $evaluation->status?->value,
            'application_status' => $application->fresh()->status?->value,
            'decision_applied' => false,
            'weighted_score' => $weightedScore,
            'admission_threshold' => $admissionThreshold,
            'waitlist_threshold' => $waitlistThreshold,
            'recommendation' => [
                'code' => $recommendation['code'],
                'label' => $recommendation['label'],
            ],
            'summary_comment' => $validated['summary_comment'] ?? null,
            'criteria_scores' => array_map(fn (array $item): array => [
                'criterion_id' => $item['criterion']->id,
                'name' => $item['criterion']->name,
                'score' => $item['score'],
                'max_score' => $item['criterion']->max_score,
                'weight' => $item['criterion']->weight,
                'comment' => $item['comment'],
            ], $normalizedItems),
            'note' => 'Évaluation enregistrée en brouillon sans changement de statut. Un membre du jury humain doit valider la décision finale.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'application_id' => $schema->integer()
                ->description('Identifiant numérique unique de la candidature à évaluer')
                ->required(),
            'criteria_scores' => $schema->array()
                ->items($schema->object([
                    'criterion_id' => $schema->integer()
                        ->description('Identifiant du critère issu de la grille du programme')
                        ->required(),
                    'score' => $schema->number()
                        ->description('Note attribuée pour ce critère (entre 0 et max_score)')
                        ->required(),
                    'comment' => $schema->string()
                        ->description('Justification / commentaire précis pour ce critère'),
                ]))
                ->description('Notes et justifications pour chaque critère de la grille du programme')
                ->required(),
            'summary_comment' => $schema->string()
                ->description('Synthèse globale et raisons principales (points forts, réserves, axes d\'amélioration) pour aider la décision humaine'),
        ];
    }
}
