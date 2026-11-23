<?php

namespace App\Mcp\Tools\Applications;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
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
            'program_id' => 'required|integer',
            'cohort_id' => 'nullable|integer',
        ]);

        /** @var Program|null $program */
        $program = Program::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['program_id'])
            ->with('evaluationCriteria')
            ->first();

        if (! $program) {
            return Response::error("Programme #{$validated['program_id']} introuvable ou non rattaché à cette structure.");
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

        if (! empty($validated['cohort_id'])) {
            $applicationsQuery->where('cohort_id', (int) $validated['cohort_id']);
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
            'program_id' => $schema->integer()
                ->description('Identifiant du programme dont on souhaite générer la fiche Excel classée')
                ->required(),
            'cohort_id' => $schema->integer()
                ->description('Optionnel : filtrer sur une cohorte précise du programme'),
        ];
    }
}
