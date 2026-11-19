<?php

namespace App\Mcp\Tools\Ui;

use App\Mcp\Apps\FormCreatorAppResource;
use App\Mcp\Concerns\InteractsWithSae;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('open_form_creator')]
#[Title('Afficher le formulaire de création de formulaire')]
#[Description('À utiliser EN PRIORITÉ dès que l\'utilisateur souhaite créer un formulaire ou questionnaire sans avoir fourni toutes les informations et questions : ouvre une carte interactive complète pour recueillir visuellement le titre, le type, le programme, le palier et les champs du formulaire au lieu de poser les questions en texte.')]
#[RendersApp(FormCreatorAppResource::class)]
class OpenFormCreatorTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        return Response::text("Formulaire interactif de création de formulaire affiché pour la structure « {$sae->name} ». L'utilisateur peut renseigner les informations et ajouter ses questions directement dans la carte.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
