<?php

namespace App\Mcp\Tools\Ui;

use App\Mcp\Apps\ProgramCreatorAppResource;
use App\Mcp\Concerns\InteractsWithSae;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\RendersApp;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('open_program_creator')]
#[Title('Afficher le formulaire de création de programme')]
#[Description('Ouvre une carte interactive permettant à l\'utilisateur de remplir visuellement le nom, la description, la durée, le palier et le seuil d\'un nouveau programme sans poser les questions en texte.')]
#[RendersApp(ProgramCreatorAppResource::class)]
class OpenProgramCreatorTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        return Response::text("Formulaire interactif de création de programme affiché pour la structure « {$sae->name} ». L'utilisateur peut remplir directement les champs dans la carte.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
