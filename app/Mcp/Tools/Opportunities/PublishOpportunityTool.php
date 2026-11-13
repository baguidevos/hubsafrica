<?php

namespace App\Mcp\Tools\Opportunities;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Opportunity;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('publish_opportunity')]
#[Title('Publier une opportunité')]
#[Description('Crée et publie une opportunité (appel à projets, concours, subvention, bourse) pour les entrepreneurs de la SAE.')]
class PublishOpportunityTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'details' => 'required|string',
            'sectors' => 'nullable|array',
            'sectors.*' => 'string|max:100',
            'target_zones' => 'nullable|array',
            'target_zones.*' => 'string|max:100',
            'learn_more_url' => 'nullable|url|max:500',
            'apply_url' => 'nullable|url|max:500',
            'deadline' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $opportunity = Opportunity::create([
            'sae_id' => $sae->id,
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']).'-'.Str::lower(Str::random(6)),
            'details' => $validated['details'],
            'sectors' => $validated['sectors'] ?? [],
            'target_zones' => $validated['target_zones'] ?? [],
            'learn_more_url' => $validated['learn_more_url'] ?? null,
            'apply_url' => $validated['apply_url'] ?? null,
            'deadline' => $validated['deadline'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $this->logAudit(
            action: 'CREATE',
            entity: 'Opportunity',
            entityId: (string) $opportunity->id,
            details: [
                'title' => $opportunity->title,
                'is_active' => $opportunity->is_active,
            ]
        );

        return Response::structured([
            'message' => "L'opportunité « {$opportunity->title} » a été publiée avec succès.",
            'opportunity' => [
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
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()
                ->description('Titre de l\'opportunité (ex: Challenge AgriTech & Climat Sahel 2026)')
                ->required(),
            'details' => $schema->string()
                ->description('Description complète : critères d\'éligibilité, dotations, calendrier et modalités')
                ->required(),
            'sectors' => $schema->array()
                ->items($schema->string())
                ->description('Liste des secteurs concernés (ex: ["AgriTech", "Énergie & Climat"])'),
            'target_zones' => $schema->array()
                ->items($schema->string())
                ->description('Zones géographiques ciblées (ex: ["Afrique de l\'Ouest", "Sénégal"])'),
            'learn_more_url' => $schema->string()
                ->description('URL d\'information détaillée sur l\'opportunité'),
            'apply_url' => $schema->string()
                ->description('URL directe pour candidater'),
            'deadline' => $schema->string()
                ->description('Date limite de candidature (format YYYY-MM-DD ou ISO 8601)'),
            'is_active' => $schema->boolean()
                ->description('Publier immédiatement l\'opportunité (true) ou l\'enregistrer en brouillon (false)')
                ->default(true),
        ];
    }
}
