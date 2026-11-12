<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Level;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use App\Models\TimelineEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_project_level')]
#[Title('Faire évoluer le niveau de maturité d\'un projet')]
#[Description('Met à jour le niveau de maturité d\'une startup (ex: Idée, Prototype, MVP, Commercialisation), historise le changement et génère un jalon dans la timeline.')]
class UpdateProjectLevelTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'project_id' => 'required|integer',
            'level_id' => 'required|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        /** @var Project|null $project */
        $project = Project::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['project_id'])
            ->first();

        if (! $project) {
            return Response::error("Projet #{$validated['project_id']} introuvable ou non rattaché à cette SAE.");
        }

        /** @var Level|null $level */
        $level = Level::find($validated['level_id']);
        if (! $level) {
            return Response::error("Niveau de maturité #{$validated['level_id']} inexistant.");
        }

        $oldLevelName = $project->currentLevel?->name ?? 'Aucun';
        $project->current_level_id = $level->id;
        $project->current_level_entered_at = now();
        $project->save();

        ProjectLevelHistory::create([
            'sae_id' => $sae->id,
            'project_id' => $project->id,
            'level_id' => $level->id,
            'entered_at' => now(),
            'notes' => $validated['notes'] ?? 'Évolution de niveau validée via l\'agent IA.',
        ]);

        TimelineEvent::create([
            'sae_id' => $sae->id,
            'project_id' => $project->id,
            'entrepreneur_id' => $project->entrepreneur_id,
            'title' => "Progression : Niveau {$level->name}",
            'description' => "Le projet a franchi une nouvelle étape de maturité ({$oldLevelName} → {$level->name}).".(! empty($validated['notes']) ? " Note : {$validated['notes']}" : ''),
            'type' => 'MILESTONE',
            'icon' => 'heroicon-m-arrow-trending-up',
        ]);

        $this->logAudit(
            action: 'UPDATE_LEVEL',
            entity: 'Project',
            entityId: (string) $project->id,
            details: [
                'old_level' => $oldLevelName,
                'new_level' => $level->name,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return Response::text("Le niveau du projet « {$project->name} » a été mis à jour avec succès : {$oldLevelName} → {$level->name}.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Identifiant numérique unique du projet')
                ->required(),
            'level_id' => $schema->integer()
                ->description('Identifiant du nouveau niveau de maturité')
                ->required(),
            'notes' => $schema->string()
                ->description('Commentaires ou justifications sur le passage au niveau supérieur'),
        ];
    }
}
