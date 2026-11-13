<?php

namespace App\Mcp\Tools\Programs;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\EvaluationCriterion;
use App\Models\Program;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('configure_program_selection_grid')]
#[Title('Paramétrer la grille de sélection d\'un programme')]
#[Description('Configure les critères d\'évaluation (nom, pondération, barème maximal, ordre, consignes) et les seuils d\'admission d\'un programme de la SAE.')]
class ConfigureProgramSelectionGridTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'program_id' => 'required|integer',
            'criteria' => 'required|array|min:1',
            'criteria.*.name' => 'required|string|max:255',
            'criteria.*.description' => 'nullable|string|max:1000',
            'criteria.*.weight' => 'nullable|integer|min:1|max:10',
            'criteria.*.max_score' => 'nullable|integer|min:1|max:100',
            'criteria.*.position' => 'nullable|integer|min:1',
            'replace_existing' => 'nullable|boolean',
            'admission_threshold' => 'nullable|integer|min:0|max:100',
            'waitlist_threshold' => 'nullable|integer|min:0|max:100',
        ]);

        /** @var Program|null $program */
        $program = Program::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['program_id'])
            ->first();

        if (! $program) {
            return Response::error("Programme #{$validated['program_id']} introuvable ou non rattaché à cette SAE.");
        }

        $effectiveAdmission = $validated['admission_threshold'] ?? $program->getAdmissionThreshold();
        $effectiveWaitlist = $validated['waitlist_threshold'] ?? $program->getWaitlistThreshold();

        if ($effectiveWaitlist > $effectiveAdmission) {
            return Response::error("Le seuil de liste d'attente ({$effectiveWaitlist}) ne peut pas dépasser le seuil d'admission ({$effectiveAdmission}).");
        }

        $replaceExisting = $validated['replace_existing'] ?? true;

        $savedCriteria = DB::transaction(function () use ($program, $sae, $validated, $replaceExisting) {
            if (isset($validated['admission_threshold']) || isset($validated['waitlist_threshold'])) {
                $program->update([
                    'admission_threshold' => $validated['admission_threshold'] ?? $program->admission_threshold,
                    'waitlist_threshold' => $validated['waitlist_threshold'] ?? $program->waitlist_threshold,
                ]);
            }

            if ($replaceExisting) {
                $program->evaluationCriteria()->delete();
                $nextPosition = 1;
            } else {
                $nextPosition = ((int) $program->evaluationCriteria()->max('position')) + 1;
            }

            foreach ($validated['criteria'] as $index => $criterionInput) {
                EvaluationCriterion::create([
                    'sae_id' => $sae->id,
                    'program_id' => $program->id,
                    'name' => $criterionInput['name'],
                    'description' => $criterionInput['description'] ?? null,
                    'weight' => $criterionInput['weight'] ?? 1,
                    'max_score' => $criterionInput['max_score'] ?? 10,
                    'position' => $criterionInput['position'] ?? ($nextPosition + $index),
                ]);
            }

            return $program->evaluationCriteria()->get();
        });

        $this->logAudit(
            action: 'CONFIGURE_SELECTION_GRID',
            entity: 'Program',
            entityId: (string) $program->id,
            details: [
                'program_name' => $program->name,
                'replace_existing' => $replaceExisting,
                'criteria_count' => $savedCriteria->count(),
                'admission_threshold' => $program->admission_threshold,
                'waitlist_threshold' => $program->waitlist_threshold,
            ]
        );

        return Response::structured([
            'message' => "La grille de sélection du programme « {$program->name} » a été paramétrée ({$savedCriteria->count()} critère(s)).",
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'admission_threshold' => $program->getAdmissionThreshold(),
                'waitlist_threshold' => $program->getWaitlistThreshold(),
            ],
            'criteria' => $savedCriteria->map(fn (EvaluationCriterion $criterion): array => [
                'id' => $criterion->id,
                'name' => $criterion->name,
                'description' => $criterion->description,
                'weight' => $criterion->weight,
                'max_score' => $criterion->max_score,
                'position' => $criterion->position,
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'program_id' => $schema->integer()
                ->description('Identifiant numérique unique du programme à paramétrer')
                ->required(),
            'criteria' => $schema->array()
                ->items($schema->object([
                    'name' => $schema->string()
                        ->description('Intitulé du critère d\'évaluation (ex: Qualité de l\'équipe fondatrice)')
                        ->required(),
                    'description' => $schema->string()
                        ->description('Consignes et guide de notation pour le jury'),
                    'weight' => $schema->integer()
                        ->description('Coefficient de pondération entre 1 et 10 (défaut: 1)')
                        ->default(1),
                    'max_score' => $schema->integer()
                        ->description('Note maximale du barème pour ce critère (défaut: 10)')
                        ->default(10),
                    'position' => $schema->integer()
                        ->description('Ordre d\'affichage du critère dans la grille'),
                ]))
                ->description('Liste des critères composant la grille de sélection du jury')
                ->required(),
            'replace_existing' => $schema->boolean()
                ->description('Remplacer la grille existante (true) ou ajouter aux critères existants (false)')
                ->default(true),
            'admission_threshold' => $schema->integer()
                ->description('Seuil minimal sur 100 pour admission directe (optionnel)'),
            'waitlist_threshold' => $schema->integer()
                ->description('Seuil minimal sur 100 pour mise en liste d\'attente (optionnel)'),
        ];
    }
}
