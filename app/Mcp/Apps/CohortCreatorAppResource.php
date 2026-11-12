<?php

namespace App\Mcp\Apps;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Program;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Ui\Enums\Library;

#[Name('cohort_creator_ui')]
#[Title('Créer une cohorte — Formulaire interactif')]
#[Description('Affiche une carte interactive pour créer une nouvelle cohorte / promotion rattachée à un programme de la SAE.')]
#[Uri('ui://hubafrica/cohort-creator')]
#[AppMeta(prefersBorder: true, libraries: [Library::Tailwind, Library::Alpine])]
class CohortCreatorAppResource extends AppResource
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $programs = Program::query()
            ->where('sae_id', $sae->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Program $program) => [
                'id' => $program->id,
                'name' => $program->name,
            ])
            ->all();

        return Response::view('mcp.apps.cohort-creator', [
            'title' => $this->title(),
            'saeName' => $sae->name,
            'programs' => $programs,
        ]);
    }
}
