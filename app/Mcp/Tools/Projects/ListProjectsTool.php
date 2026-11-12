<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_projects')]
#[Title('Lister les startups et projets accompagnés')]
#[Description('Recherche et liste les projets accompagnés par la SAE selon leur maturité (niveau), leur secteur ou leur statut.')]
class ListProjectsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'sector' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:ACTIF,ARCHIVE,DIPLOME,ABANDON,SUSPENDU,TOUS',
            'level_id' => 'nullable|integer',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $queryBuilder = Project::query()
            ->where('sae_id', $sae->id)
            ->with(['currentLevel', 'entrepreneur.user'])
            ->latest('updated_at');

        if (! empty($validated['query'])) {
            $search = $validated['query'];
            $queryBuilder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('problem', 'like', "%{$search}%")
                    ->orWhere('solution', 'like', "%{$search}%")
                    ->orWhereHas('entrepreneur.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%"));
            });
        }

        if (! empty($validated['sector'])) {
            $queryBuilder->where('sector', 'like', "%{$validated['sector']}%");
        }

        if (! empty($validated['status']) && $validated['status'] !== 'TOUS') {
            $queryBuilder->where('status', $validated['status']);
        }

        if (! empty($validated['level_id'])) {
            $queryBuilder->where('current_level_id', $validated['level_id']);
        }

        $limit = $validated['limit'] ?? 20;
        $projects = $queryBuilder->take($limit)->get();

        $data = $projects->map(fn (Project $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'status' => $p->status?->value ?? (string) $p->status,
            'sector' => $p->sector,
            'territory' => $p->territory,
            'current_level' => $p->currentLevel?->name ?? 'N/A',
            'team_size' => $p->team_size,
            'entrepreneur_name' => $p->entrepreneur?->user?->name ?? 'N/A',
            'entrepreneur_email' => $p->entrepreneur?->user?->email ?? 'N/A',
            'created_at' => $p->created_at?->toIso8601String(),
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'projects' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Mot-clé de recherche (nom de la startup, porteur, description)'),
            'sector' => $schema->string()
                ->description('Filtrer par secteur d\'activité (ex: AgriTech, Fintech, Santé, etc.)'),
            'status' => $schema->string()
                ->enum(['ACTIF', 'ARCHIVE', 'DIPLOME', 'ABANDON', 'SUSPENDU', 'TOUS'])
                ->description('Filtrer par statut opérationnel du projet')
                ->default('TOUS'),
            'level_id' => $schema->integer()
                ->description('Identifiant du niveau de maturité actuel'),
            'limit' => $schema->integer()
                ->description('Nombre maximum de projets à retourner')
                ->default(20),
        ];
    }
}
