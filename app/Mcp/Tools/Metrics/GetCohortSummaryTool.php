<?php

namespace App\Mcp\Tools\Metrics;

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

#[Name('get_cohort_summary')]
#[Title('Bilan synthétique d\'une promotion / cohorte')]
#[Description('Génère une synthèse complète d\'une cohorte : programme, taux de remplissage, liste des projets intégrés et coachs mobilisés.')]
class GetCohortSummaryTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'cohort_id' => 'required|integer',
        ]);

        /** @var Cohort|null $cohort */
        $cohort = Cohort::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['cohort_id'])
            ->with([
                'program',
                'projects.currentLevel',
                'projects.entrepreneur.user',
                'projects.coachAssignments.coach',
            ])
            ->first();

        if (! $cohort) {
            return Response::error("Cohorte #{$validated['cohort_id']} introuvable ou non rattachée à cette structure.");
        }

        $projects = $cohort->projects->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'sector' => $p->sector,
            'current_level' => $p->currentLevel?->name,
            'entrepreneur' => $p->entrepreneur?->user?->name,
            'coaches' => $p->coachAssignments->map(fn ($a) => $a->coach?->name)->filter()->values()->all(),
        ])->all();

        return Response::structured([
            'cohort' => [
                'id' => $cohort->id,
                'name' => $cohort->name,
                'slug' => $cohort->slug,
                'status' => $cohort->status?->value ?? (string) $cohort->status,
                'capacity' => $cohort->capacity,
                'enrolled_count' => count($projects),
                'occupancy_rate' => $cohort->capacity > 0
                    ? round((count($projects) / $cohort->capacity) * 100, 1).'%'
                    : 'N/A',
                'start_date' => $cohort->start_date?->toDateString(),
                'end_date' => $cohort->end_date?->toDateString(),
            ],
            'program' => [
                'id' => $cohort->program?->id,
                'name' => $cohort->program?->name,
                'duration' => $cohort->program?->duration,
            ],
            'projects' => $projects,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cohort_id' => $schema->integer()
                ->description('Identifiant numérique unique de la promotion / cohorte')
                ->required(),
        ];
    }
}
