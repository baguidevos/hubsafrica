<?php

namespace App\Mcp\Tools\Coaching;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Coach;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_coaches')]
#[Title('Lister les coachs et mentors de la structure')]
#[Description('Récupère le réseau de coachs affiliés à la SAE, leurs spécialités, disponibilités et projets actuellement assignés.')]
class ListCoachesTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $queryBuilder = Coach::query()
            ->where('sae_id', $sae->id)
            ->with(['user', 'assignments.project:id,name'])
            ->withCount('assignments');

        if (! empty($validated['query'])) {
            $search = $validated['query'];
            $queryBuilder->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $limit = $validated['limit'] ?? 20;
        $coaches = $queryBuilder->take($limit)->get();

        $data = $coaches->map(fn (Coach $c) => [
            'id' => $c->id,
            'name' => $c->name ?? $c->user?->name,
            'email' => $c->email ?? $c->user?->email,
            'phone' => $c->phone,
            'specialties' => $c->specialties ?? [],
            'status' => $c->status?->value ?? (string) $c->status,
            'assigned_projects_count' => $c->assignments_count,
            'assigned_projects' => $c->assignments->map(fn ($a) => $a->project?->name)->filter()->values()->all(),
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'coaches' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Recherche par nom ou adresse e-mail du coach'),
            'limit' => $schema->integer()
                ->description('Nombre maximal de coachs à renvoyer')
                ->default(20),
        ];
    }
}
