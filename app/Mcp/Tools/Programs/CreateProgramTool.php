<?php

namespace App\Mcp\Tools\Programs;

use App\Mcp\Concerns\InteractsWithSae;
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

#[Name('create_program')]
#[Title('Créer un programme d\'accompagnement')]
#[Description('Crée un nouveau programme d\'incubation ou d\'accélération au sein de la SAE avec sa description, durée et seuils.')]
class CreateProgramTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'duration' => 'nullable|string|max:50',
            'level_id' => 'nullable|integer',
            'admission_threshold' => 'nullable|integer|min:0|max:100',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after_or_equal:registration_start',
            'is_published' => 'nullable|boolean',
        ]);

        $program = Program::create([
            'sae_id' => $sae->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(5)),
            'description' => $validated['description'],
            'objectives' => $validated['objectives'] ?? null,
            'duration' => $validated['duration'] ?? '6 mois',
            'level_id' => $validated['level_id'] ?? null,
            'admission_threshold' => $validated['admission_threshold'] ?? 60,
            'registration_start' => $validated['registration_start'] ?? null,
            'registration_end' => $validated['registration_end'] ?? null,
            'is_published' => $validated['is_published'] ?? false,
        ]);

        $this->logAudit(
            action: 'CREATE',
            entity: 'Program',
            entityId: (string) $program->id,
            details: [
                'name' => $program->name,
                'is_published' => $program->is_published,
            ]
        );

        return Response::structured([
            'message' => "Le programme « {$program->name} » a été créé avec succès.",
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'slug' => $program->slug,
                'is_published' => $program->is_published,
                'duration' => $program->duration,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()
                ->description('Nom du programme (ex: AgriPulse - Cohorte 1 : Agritech & Greentech)')
                ->required(),
            'description' => $schema->string()
                ->description('Présentation détaillée des objectifs et de la proposition de valeur du programme')
                ->required(),
            'objectives' => $schema->string()
                ->description('Compétences et objectifs pédagogiques visés'),
            'duration' => $schema->string()
                ->description('Durée indicative (ex: "6 mois", "12 semaines")')
                ->default('6 mois'),
            'level_id' => $schema->integer()
                ->description('Identifiant du niveau de maturité visé (ex: 1 pour Idéation, 2 pour Prototype, etc.)'),
            'admission_threshold' => $schema->integer()
                ->description('Note minimale sur 100 requise pour admission directe')
                ->default(60),
            'registration_start' => $schema->string()
                ->description('Date d\'ouverture des candidatures (format YYYY-MM-DD)'),
            'registration_end' => $schema->string()
                ->description('Date de clôture des candidatures (format YYYY-MM-DD)'),
            'is_published' => $schema->boolean()
                ->description('Rendre le programme immédiatement visible sur le portail public')
                ->default(false),
        ];
    }
}
