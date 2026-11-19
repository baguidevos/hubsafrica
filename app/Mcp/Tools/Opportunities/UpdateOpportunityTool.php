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

#[Name('update_opportunity')]
#[Title('Modifier une opportunité')]
#[Description('Modifie une opportunité existante de la SAE (titre, détails en Markdown, secteurs, zones géographiques, URLs, date limite ou statut de publication).')]
class UpdateOpportunityTool extends Tool
{
    use InteractsWithSae;

    private const UPDATABLE_FIELDS = [
        'title',
        'details',
        'sectors',
        'target_zones',
        'learn_more_url',
        'apply_url',
        'deadline',
        'is_active',
    ];

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'opportunity_id' => 'required|integer',
            'title' => 'sometimes|required|string|max:255',
            'details' => 'sometimes|required|string',
            'sectors' => 'sometimes|nullable|array',
            'sectors.*' => 'string|max:100',
            'target_zones' => 'sometimes|nullable|array',
            'target_zones.*' => 'string|max:100',
            'learn_more_url' => 'sometimes|nullable|url|max:500',
            'apply_url' => 'sometimes|nullable|url|max:500',
            'deadline' => 'sometimes|nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $opportunity = Opportunity::query()
            ->where('sae_id', $sae->id)
            ->find($validated['opportunity_id']);

        if (! $opportunity) {
            return Response::error("Opportunité introuvable pour cette SAE (ID : {$validated['opportunity_id']}).");
        }

        $updates = $this->extractUpdates($validated);

        if ($updates === []) {
            return Response::error('Aucun champ à modifier n\'a été fourni.');
        }

        $opportunity->update($updates);
        $opportunity->refresh();

        $this->logAudit(
            action: 'UPDATE',
            entity: 'Opportunity',
            entityId: (string) $opportunity->id,
            details: [
                'title' => $opportunity->title,
                'updated_fields' => array_keys($updates),
                'is_active' => (bool) $opportunity->is_active,
            ],
        );

        return Response::structured([
            'message' => "L'opportunité « {$opportunity->title} » a été mise à jour avec succès.",
            'updated_fields' => array_keys($updates),
            'opportunity' => [
                'id' => $opportunity->id,
                'title' => $opportunity->title,
                'slug' => $opportunity->slug,
                'share_url' => $opportunity->share_url,
                'details' => $opportunity->details,
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

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function extractUpdates(array $validated): array
    {
        $updates = [];

        foreach (self::UPDATABLE_FIELDS as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            if (in_array($field, ['sectors', 'target_zones'], true)) {
                $updates[$field] = $validated[$field] ?? [];

                continue;
            }

            $updates[$field] = $validated[$field];
        }

        return $updates;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'opportunity_id' => $schema->integer()
                ->description('Identifiant numérique de l\'opportunité à modifier (obtenu via list_opportunities)')
                ->required(),
            'title' => $schema->string()
                ->description('Nouveau titre de l\'opportunité'),
            'details' => $schema->string()
                ->description('Nouvelle description complète en Markdown (titres, listes, gras, liens, critères, dotations)'),
            'sectors' => $schema->array()
                ->items($schema->string())
                ->description('Nouvelle liste des secteurs concernés'),
            'target_zones' => $schema->array()
                ->items($schema->string())
                ->description('Nouvelle liste des zones géographiques ciblées'),
            'learn_more_url' => $schema->string()
                ->description('Nouvelle URL d\'information détaillée (ou null pour effacer)'),
            'apply_url' => $schema->string()
                ->description('Nouvelle URL directe pour candidater (ou null pour effacer)'),
            'deadline' => $schema->string()
                ->description('Nouvelle date limite de candidature (YYYY-MM-DD ou ISO 8601, ou null pour effacer)'),
            'is_active' => $schema->boolean()
                ->description('Activer (true) ou désactiver/archiver (false) l\'opportunité'),
        ];
    }
}
