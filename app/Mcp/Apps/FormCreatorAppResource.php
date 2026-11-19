<?php

namespace App\Mcp\Apps;

use App\Enums\FormType;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Level;
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

#[Name('form_creator_ui')]
#[Title('Créer un formulaire — Interface interactive')]
#[Description('Affiche une carte interactive permettant de configurer le titre, le type, le programme associé et les questions d\'un nouveau formulaire pour la SAE.')]
#[Uri('ui://hubafrica/form-creator')]
#[AppMeta(prefersBorder: true, libraries: [Library::Tailwind, Library::Alpine])]
class FormCreatorAppResource extends AppResource
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

        $levels = Level::query()
            ->where('sae_id', $sae->id)
            ->orderBy('order')
            ->get(['id', 'name'])
            ->map(fn (Level $level) => [
                'id' => $level->id,
                'name' => $level->name,
            ])
            ->all();

        $formTypes = collect(FormType::cases())
            ->map(fn (FormType $type) => [
                'value' => $type->value,
                'label' => $type->getLabel(),
            ])
            ->all();

        return Response::view('mcp.apps.form-creator', [
            'title' => $this->title(),
            'saeName' => $sae->name,
            'programs' => $programs,
            'levels' => $levels,
            'formTypes' => $formTypes,
        ]);
    }
}
