<?php

namespace App\Mcp\Tools\Opportunities;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Opportunity;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_opportunities')]
#[Title('Lister les opportunités')]
#[Description('Récupère les opportunités publiées ou en brouillon de la SAE avec filtres par statut (ouvertes, clôturées) et recherche textuelle.')]
class ListOpportunitiesTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'status' => 'nullable|string|in:open,closed,all',
            'search' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Opportunity::query()
            ->where('sae_id', $sae->id)
            ->latest();

        $status = $validated['status'] ?? 'all';
        if ($status === 'open') {
            $query->open();
        } elseif ($status === 'closed') {
            $query->closed();
        }

        if (isset($validated['is_active'])) {
            $query->where('is_active', $validated['is_active']);
        }

        if (! empty($validated['search'])) {
            $query->search($validated['search']);
        }

        $limit = $validated['limit'] ?? 20;
        $opportunities = $query->take($limit)->get();

        $items = $opportunities->map(fn (Opportunity $opportunity): array => [
            'id' => $opportunity->id,
            'title' => $opportunity->title,
            'slug' => $opportunity->slug,
            'share_url' => $opportunity->share_url,
            'sectors' => $opportunity->sectors ?? [],
            'target_zones' => $opportunity->target_zones ?? [],
            'learn_more_url' => $opportunity->learn_more_url,
            'apply_url' => $opportunity->apply_url,
            'deadline' => $opportunity->deadline?->toIso8601String(),
            'deadline_status' => $opportunity->getDeadlineStatusLabel(),
            'is_active' => (bool) $opportunity->is_active,
            'is_open' => $opportunity->isOpen(),
            'created_at' => $opportunity->created_at?->toIso8601String(),
        ])->all();

        return Response::structured([
            'count' => count($items),
            'sae' => $sae->name,
            'opportunities' => $items,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'status' => $schema->string()
                ->enum(['open', 'closed', 'all'])
                ->description('Filtrer par statut d\'ouverture : open (ouvertes), closed (clôturées) ou all (toutes)')
                ->default('all'),
            'search' => $schema->string()
                ->description('Recherche textuelle dans le titre, les détails, les secteurs ou les zones ciblées'),
            'is_active' => $schema->boolean()
                ->description('Filtrer par visibilité (true pour actives, false pour désactivées/brouillons)'),
            'limit' => $schema->integer()
                ->description('Nombre maximal d\'opportunités à renvoyer')
                ->default(20),
        ];
    }
}
