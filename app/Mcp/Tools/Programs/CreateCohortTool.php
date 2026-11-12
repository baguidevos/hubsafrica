<?php

namespace App\Mcp\Tools\Programs;

use App\Enums\CohortStatus;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Cohort;
use App\Models\Program;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('create_cohort')]
#[Title('Créer une cohorte / promotion')]
#[Description('Crée une nouvelle promotion rattachée à un programme d\'accompagnement avec sa capacité d\'accueil et ses dates.')]
class CreateCohortTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'program_id' => 'required|integer',
            'name' => 'required|string|max:150',
            'capacity' => 'nullable|integer|min:1|max:500',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:OUVERTE,EN_COURS,CLOTUREE',
            'objectives' => 'nullable|string',
        ]);

        /** @var Program|null $program */
        $program = Program::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['program_id'])
            ->first();

        if (! $program) {
            return Response::error("Programme #{$validated['program_id']} introuvable ou non rattaché à cette SAE.");
        }

        $status = ! empty($validated['status'])
            ? CohortStatus::from($validated['status'])
            : CohortStatus::OUVERTE;

        $cohort = Cohort::create([
            'sae_id' => $sae->id,
            'program_id' => $program->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'number' => Cohort::where('program_id', $program->id)->count() + 1,
            'capacity' => $validated['capacity'] ?? 20,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $status,
            'objectives' => $validated['objectives'] ?? null,
        ]);

        $this->logAudit(
            action: 'CREATE',
            entity: 'Cohort',
            entityId: (string) $cohort->id,
            details: [
                'name' => $cohort->name,
                'program' => $program->name,
                'capacity' => $cohort->capacity,
            ]
        );

        return Response::structured([
            'message' => "La cohorte « {$cohort->name} » a été créée avec succès pour le programme « {$program->name} ».",
            'cohort' => [
                'id' => $cohort->id,
                'name' => $cohort->name,
                'status' => $cohort->status?->value ?? (string) $cohort->status,
                'capacity' => $cohort->capacity,
                'start_date' => $cohort->start_date?->toDateString(),
                'end_date' => $cohort->end_date?->toDateString(),
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'program_id' => $schema->integer()
                ->description('Identifiant numérique unique du programme parent')
                ->required(),
            'name' => $schema->string()
                ->description('Nom de la cohorte (ex: Promotion Printemps 2026, Cohorte Agritech #1)')
                ->required(),
            'capacity' => $schema->integer()
                ->description('Nombre maximal de startups admises dans la cohorte')
                ->default(20),
            'start_date' => $schema->string()
                ->description('Date de démarrage prévisionnelle (format YYYY-MM-DD)'),
            'end_date' => $schema->string()
                ->description('Date de fin de l\'accompagnement (format YYYY-MM-DD)'),
            'status' => $schema->string()
                ->enum(['OUVERTE', 'EN_COURS', 'CLOTUREE'])
                ->description('Statut initial de la cohorte')
                ->default('OUVERTE'),
            'objectives' => $schema->string()
                ->description('Objectifs spécifiques assignés à cette promotion'),
        ];
    }
}
