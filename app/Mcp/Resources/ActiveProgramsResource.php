<?php

namespace App\Mcp\Resources;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Program;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Name('active_programs')]
#[Title('Programmes et cohortes ouverts aux candidatures')]
#[Description('Liste en temps réel les programmes d\'accompagnement actuellement ouverts aux inscriptions pour la SAE.')]
#[Uri('hubafrica://sae/programs/active')]
#[MimeType('application/json')]
class ActiveProgramsResource extends Resource
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $activePrograms = Program::query()
            ->where('sae_id', $sae->id)
            ->where('is_published', true)
            ->with(['level', 'cohorts' => fn ($q) => $q->where('status', 'OUVERTE')])
            ->get()
            ->filter(fn (Program $p) => $p->isAcceptingApplications())
            ->map(fn (Program $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'level' => $p->level?->name,
                'duration' => $p->duration,
                'registration_end' => $p->registration_end?->toDateString(),
                'open_cohorts' => $p->cohorts->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'capacity' => $c->capacity,
                ])->values()->all(),
            ])
            ->values()
            ->all();

        return Response::text(json_encode([
            'sae' => $sae->name,
            'active_programs' => $activePrograms,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
