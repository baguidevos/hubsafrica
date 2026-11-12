<?php

namespace App\Mcp\Tools\Ui;

use App\Mcp\Apps\CohortCreatorAppResource;
use App\Mcp\Concerns\InteractsWithSae;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('open_cohort_creator')]
#[Title('Afficher le formulaire de création de cohorte')]
#[Description('Ouvre une carte interactive permettant à l\'utilisateur de sélectionner le programme et remplir visuellement les détails d\'une nouvelle cohorte sans poser les questions en texte.')]
#[RendersApp(CohortCreatorAppResource::class)]
class OpenCohortCreatorTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        return Response::text("Formulaire interactif de création de cohorte affiché pour la structure « {$sae->name} ». L'utilisateur peut sélectionner le programme et soumettre directement.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
