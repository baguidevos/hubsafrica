<?php

namespace App\Mcp\Tools\Applications;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Program;
use App\Services\ApplicationGridEvaluationService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('export_application_evaluations_sheet')]
#[Title('Générer la fiche Excel classée des candidatures évaluées (Réussi / Liste d\'attente / Recalé)')]
#[Description('Compile et classe les candidatures d\'un programme (ou d\'une cohorte) selon la grille d\'évaluation du programme, du réussi au recalé avec les notes par critère, raisons et commentaires, et génère une fiche Excel (CSV) téléchargeable pour la validation finale humaine.')]
class ExportApplicationEvaluationsSheetTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request, ApplicationGridEvaluationService $evaluationService): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'cohort_id' => 'nullable|integer',
            'cohort_name' => 'nullable|string|max:255',
            'program_id' => 'nullable|integer',
        ]);

        if (empty($validated['cohort_id']) && empty($validated['cohort_name']) && empty($validated['program_id'])) {
            return Response::error('Veuillez préciser le nom ou l\'identifiant de la cohorte à exporter.');
        }

        /** @var Cohort|null $cohort */
        $cohort = null;

        if (! empty($validated['cohort_id'])) {
            $cohort = Cohort::query()
                ->where('sae_id', $sae->id)
                ->where('id', (int) $validated['cohort_id'])
                ->with('program.evaluationCriteria')
                ->first();

            if (! $cohort) {
                return Response::error("Cohorte #{$validated['cohort_id']} introuvable ou non rattachée à cette structure.");
            }
        } elseif (! empty($validated['cohort_name'])) {
            $searchName = trim((string) $validated['cohort_name']);
            $matches = Cohort::query()
                ->where('sae_id', $sae->id)
                ->where('name', 'like', "%{$searchName}%")
                ->with('program.evaluationCriteria')
                ->get();

            if ($matches->isEmpty()) {
                return Response::error("Aucune cohorte trouvée correspondant à « {$searchName} ».");
            }

            if ($matches->count() > 1) {
                $exact = $matches->first(fn (Cohort $c): bool => mb_strtolower($c->name) === mb_strtolower($searchName));
                if (! $exact) {
                    $names = $matches->pluck('name')->implode(', ');

                    return Response::error("Plusieurs cohortes correspondent à « {$searchName} » ({$names}). Veuillez préciser le nom exact de la cohorte.");
                }
                $cohort = $exact;
            } else {
                $cohort = $matches->first();
            }
        }

        /** @var Program|null $program */
        $program = $cohort?->program;

        if (! $program && ! empty($validated['program_id'])) {
            $program = Program::query()
                ->where('sae_id', $sae->id)
                ->where('id', (int) $validated['program_id'])
                ->with('evaluationCriteria')
                ->first();
        }

        if (! $program) {
            return Response::error('Programme associé introuvable pour cette cohorte.');
        }

        $applicationsQuery = Application::query()
            ->where('sae_id', $sae->id)
            ->where('program_id', $program->id)
            ->with([
                'cohort',
                'project',
                'entrepreneur.user',
                'evaluations.scores.criterion',
            ]);

        if ($cohort) {
            $applicationsQuery->where('cohort_id', $cohort->id);
        }

        $applications = $applicationsQuery->get();
        $criteria = $program->evaluationCriteria;
        $admissionThreshold = $program->getAdmissionThreshold();
        $waitlistThreshold = $program->getWaitlistThreshold();

        $rankedRows = $evaluationService->buildRankedRows(
            applications: $applications,
            criteria: $criteria,
            admissionThreshold: $admissionThreshold,
            waitlistThreshold: $waitlistThreshold,
        );

        $csvContent = $evaluationService->generateExcelCsv($rankedRows, $criteria);
        $slug = Str::slug($program->name) ?: "programme-{$program->id}";
        $timestamp = now()->format('Ymd-His');
        $relativePath = "exports/evaluations/sae-{$sae->id}/evaluation-{$slug}-{$timestamp}.csv";

        Storage::disk('public')->put($relativePath, $csvContent);

        $summary = [
            'total' => count($rankedRows),
            'reussi' => count(array_filter($rankedRows, fn (array $r): bool => $r['recommendation_code'] === 'REUSSI')),
            'liste_attente' => count(array_filter($rankedRows, fn (array $r): bool => $r['recommendation_code'] === 'LISTE_ATTENTE')),
            'recale' => count(array_filter($rankedRows, fn (array $r): bool => $r['recommendation_code'] === 'RECALE')),
            'a_evaluer' => count(array_filter($rankedRows, fn (array $r): bool => $r['recommendation_code'] === 'A_EVALUER')),
        ];

        return Response::structured([
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'admission_threshold' => $admissionThreshold,
                'waitlist_threshold' => $waitlistThreshold,
            ],
            'summary' => $summary,
            'download_url' => Storage::disk('public')->url($relativePath),
            'file_path' => $relativePath,
            'criteria' => $criteria->map(fn ($criterion): array => [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'weight' => $criterion->weight,
                'max_score' => $criterion->max_score,
            ])->values()->all(),
            'ranked_applications' => $rankedRows,
            'note' => 'Classement indicatif généré pour revue humaine. Aucune décision finale n\'a été appliquée automatiquement.',
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cohort_name' => $schema->string()
                ->description('Nom de la cohorte ciblée (recommandé : résout automatiquement le programme associé)'),
            'cohort_id' => $schema->integer()
                ->description('Identifiant numérique de la cohorte ciblée'),
            'program_id' => $schema->integer()
                ->description('Optionnel : identifiant du programme si aucune cohorte n\'est précisée'),
        ];
    }
}
