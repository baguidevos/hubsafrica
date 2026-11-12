<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\DeliverableSubmission;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_project_deliverables')]
#[Title('Lister les livrables d\'un projet')]
#[Description('Consulte les livrables déposés par une startup (Business Plan, pitch deck, étude de marché, états financiers), leur statut de validation et versions.')]
class ListProjectDeliverablesTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'project_id' => 'required|integer',
        ]);

        /** @var Project|null $project */
        $project = Project::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['project_id'])
            ->first();

        if (! $project) {
            return Response::error("Projet #{$validated['project_id']} introuvable ou non rattaché à cette SAE.");
        }

        $submissions = DeliverableSubmission::query()
            ->where('sae_id', $sae->id)
            ->where('project_id', $project->id)
            ->with(['definition', 'cohortPhase'])
            ->latest('submitted_at')
            ->get();

        $data = $submissions->map(fn (DeliverableSubmission $s) => [
            'id' => $s->id,
            'title' => $s->definition?->title ?? $s->file_name ?? 'Livrable',
            'version' => $s->version,
            'status' => $s->status?->value ?? (string) $s->status,
            'is_late' => (bool) $s->is_late,
            'file_name' => $s->file_name,
            'comment' => $s->comment,
            'phase_name' => $s->cohortPhase?->name ?? 'Général',
            'submitted_at' => $s->submitted_at?->toIso8601String(),
        ])->all();

        return Response::structured([
            'project_id' => $project->id,
            'project_name' => $project->name,
            'count' => count($data),
            'deliverables' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Identifiant numérique unique du projet')
                ->required(),
        ];
    }
}
