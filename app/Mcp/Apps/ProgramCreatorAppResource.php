<?php

namespace App\Mcp\Apps;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Level;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\AppResource;
use Laravel\Mcp\Server\Attributes\AppMeta;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Ui\Enums\Library;

#[Name('program_creator_ui')]
#[Title('Créer un programme — Formulaire interactif')]
#[Description('Affiche une carte interactive pour créer un nouveau programme d\'accompagnement au sein de la SAE.')]
#[Uri('ui://hubafrica/program-creator')]
#[AppMeta(prefersBorder: true, libraries: [Library::Tailwind, Library::Alpine])]
class ProgramCreatorAppResource extends AppResource
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $levels = Level::query()
            ->where('sae_id', $sae->id)
            ->orderBy('order')
            ->get(['id', 'name'])
            ->map(fn (Level $level) => [
                'id' => $level->id,
                'name' => $level->name,
            ])
            ->all();

        return Response::view('mcp.apps.program-creator', [
            'title' => $this->title(),
            'saeName' => $sae->name,
            'levels' => $levels,
        ]);
    }
}
