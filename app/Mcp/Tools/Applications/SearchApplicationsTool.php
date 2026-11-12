<?php

namespace App\Mcp\Tools\Applications;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('search_applications')]
#[Title('Rechercher des candidatures')]
#[Description('Recherche et filtre les candidatures soumises à la SAE par cohorte, statut, mot-clé ou note.')]
class SearchApplicationsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'query' => 'nullable|string|max:100',
            'cohort_id' => 'nullable|integer',
            'status' => 'nullable|string|in:BROUILLON,SOUMISE,EN_REVUE,ACCEPTEE,REFUSEE,EN_ATTENTE,TOUS',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $queryBuilder = Application::query()
            ->where('sae_id', $sae->id)
            ->with(['program:id,name', 'cohort:id,name', 'project:id,name,sector', 'entrepreneur.user:id,name,email'])
            ->latest('submitted_at');

        if (! empty($validated['query'])) {
            $search = $validated['query'];
            $queryBuilder->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('project', fn ($pq) => $pq->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('entrepreneur.user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if (! empty($validated['cohort_id'])) {
            $queryBuilder->where('cohort_id', $validated['cohort_id']);
        }

        if (! empty($validated['status']) && $validated['status'] !== 'TOUS') {
            $queryBuilder->where('status', $validated['status']);
        }

        $limit = $validated['limit'] ?? 20;
        $applications = $queryBuilder->take($limit)->get();

        $data = $applications->map(fn (Application $app) => [
            'id' => $app->id,
            'code' => $app->code,
            'status' => $app->status?->value ?? (string) $app->status,
            'score' => $app->score,
            'candidate_name' => $app->entrepreneur?->user?->name ?? 'N/A',
            'candidate_email' => $app->entrepreneur?->user?->email ?? 'N/A',
            'project_name' => $app->project?->name ?? 'N/A',
            'project_sector' => $app->project?->sector ?? 'N/A',
            'program_name' => $app->program?->name ?? 'N/A',
            'cohort_name' => $app->cohort?->name ?? 'N/A',
            'submitted_at' => $app->submitted_at?->toIso8601String(),
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'applications' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()
                ->description('Mot-clé de recherche (nom de startup, nom ou email du candidat, code dossier)'),
            'cohort_id' => $schema->integer()
                ->description('Filtrer par identifiant unique de la cohorte'),
            'status' => $schema->string()
                ->enum(['BROUILLON', 'SOUMISE', 'EN_REVUE', 'ACCEPTEE', 'REFUSEE', 'EN_ATTENTE', 'TOUS'])
                ->description('Filtrer par statut de la candidature')
                ->default('TOUS'),
            'limit' => $schema->integer()
                ->description('Nombre maximal de dossiers à renvoyer (défaut : 20, max : 50)')
                ->default(20),
        ];
    }
}
