<?php

namespace App\Mcp\Tools\Programs;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Cohort;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_cohorts')]
#[Title('Lister les cohortes / promotions')]
#[Description('Liste les cohortes de la SAE, avec le programme parent, le nombre de startups intégrées, la capacité et les dates.')]
class ListCohortsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'program_id' => 'nullable|integer',
            'status' => 'nullable|string|in:OUVERTE,EN_COURS,CLOTUREE,TOUS',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Cohort::query()
            ->where('sae_id', $sae->id)
            ->with(['program:id,name'])
            ->withCount(['projects', 'applications'])
            ->latest('start_date');

        if (! empty($validated['program_id'])) {
            $query->where('program_id', $validated['program_id']);
        }

        if (! empty($validated['status']) && $validated['status'] !== 'TOUS') {
            $query->where('status', $validated['status']);
        }

        $limit = $validated['limit'] ?? 20;
        $cohorts = $query->take($limit)->get();

        $data = $cohorts->map(fn (Cohort $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'status' => $c->status?->value ?? (string) $c->status,
            'program_name' => $c->program?->name,
            'capacity' => $c->capacity,
            'enrolled_projects_count' => $c->projects_count,
            'applications_count' => $c->applications_count,
            'start_date' => $c->start_date?->toDateString(),
            'end_date' => $c->end_date?->toDateString(),
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'cohorts' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'program_id' => $schema->integer()
                ->description('Filtrer par identifiant du programme'),
            'status' => $schema->string()
                ->enum(['OUVERTE', 'EN_COURS', 'CLOTUREE', 'TOUS'])
                ->description('Filtrer par statut de la cohorte')
                ->default('TOUS'),
            'limit' => $schema->integer()
                ->description('Nombre maximal de cohortes à renvoyer')
                ->default(20),
        ];
    }
}
