<?php

namespace App\Mcp\Servers;

use App\Mcp\Apps\CohortCreatorAppResource;
use App\Mcp\Apps\ProgramCreatorAppResource;
use App\Mcp\Prompts\EvaluateApplicationPrompt;
use App\Mcp\Prompts\SummarizeCohortPrompt;
use App\Mcp\Resources\ActiveProgramsResource;
use App\Mcp\Resources\SaeProfileResource;
use App\Mcp\Tools\Applications\GetApplicationDetailsTool;
use App\Mcp\Tools\Applications\ScoreApplicationTool;
use App\Mcp\Tools\Applications\SearchApplicationsTool;
use App\Mcp\Tools\Applications\UpdateApplicationStatusTool;
use App\Mcp\Tools\Coaching\AssignCoachToProjectTool;
use App\Mcp\Tools\Coaching\ListCoachesTool;
use App\Mcp\Tools\Coaching\ListCoachingSessionsTool;
use App\Mcp\Tools\Coaching\LogCoachingSessionTool;
use App\Mcp\Tools\Metrics\GetCohortSummaryTool;
use App\Mcp\Tools\Metrics\GetSaeMetricsTool;
use App\Mcp\Tools\Programs\CreateCohortTool;
use App\Mcp\Tools\Programs\CreateProgramTool;
use App\Mcp\Tools\Programs\ListCohortsTool;
use App\Mcp\Tools\Programs\ListProgramsTool;
use App\Mcp\Tools\Programs\UpdateCohortStatusTool;
use App\Mcp\Tools\Projects\GetProjectDetailsTool;
use App\Mcp\Tools\Projects\ListProjectDeliverablesTool;
use App\Mcp\Tools\Projects\ListProjectsTool;
use App\Mcp\Tools\Projects\UpdateProjectLevelTool;
use App\Mcp\Tools\Ui\OpenCohortCreatorTool;
use App\Mcp\Tools\Ui\OpenProgramCreatorTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Hubs Africa SAE Server')]
#[Version('1.1.0')]
#[Instructions(<<<'INSTRUCTIONS'
Tu es l'assistant métier de la structure d'accompagnement (SAE) sur Hubs Africa. Tu aides les gestionnaires à piloter leurs programmes, cohortes, candidatures, startups incubées, coachs et indicateurs d'impact.

RÈGLES DE COMMUNICATION STRICTES :
1. Langage 100 % métier : parle toujours comme un conseiller Hubs Africa. Ne mentionne JAMAIS de détails techniques liés au système à l'utilisateur : aucun nom d'outil interne (ex. jamais `open_program_creator`, `create_cohort`, `get_sae_metrics`, etc.), aucune URI (`ui://...`), aucune route API, aucun nom de modèle, de table, de colonne, de framework (Laravel, MCP, JSON) ou de trace d'erreur.
2. En cas d'erreur ou d'indisponibilité : reste sobre et orienté utilisateur. Indique simplement que l'opération n'a pas pu aboutir et propose de réessayer ou de vérifier les informations métier, sans jamais formuler d'hypothèse technique ni exposer de message système.
3. Interfaces interactives : lorsque l'utilisateur souhaite créer un programme ou une cohorte sans avoir fourni toutes les informations nécessaires, ouvre immédiatement la carte/formulaire interactif correspondant au lieu de poser les questions une par une par texte, et présente-le naturellement sans citer le nom de l'outil.
INSTRUCTIONS)]
class SaeMcpServer extends Server
{
    public int $defaultPaginationLength = 50;

    /**
     * Outils exécutables par les clients IA.
     */
    protected array $tools = [
        // Candidatures & Sélection
        SearchApplicationsTool::class,
        GetApplicationDetailsTool::class,
        UpdateApplicationStatusTool::class,
        ScoreApplicationTool::class,

        // Projets & Startups
        ListProjectsTool::class,
        GetProjectDetailsTool::class,
        UpdateProjectLevelTool::class,
        ListProjectDeliverablesTool::class,

        // Programmes & Cohortes
        ListProgramsTool::class,
        CreateProgramTool::class,
        ListCohortsTool::class,
        CreateCohortTool::class,
        UpdateCohortStatusTool::class,

        // Coaching & Accompagnement
        ListCoachesTool::class,
        AssignCoachToProjectTool::class,
        ListCoachingSessionsTool::class,
        LogCoachingSessionTool::class,

        // Métriques & Bilan
        GetSaeMetricsTool::class,
        GetCohortSummaryTool::class,

        // Interfaces Interactives (Generative UI / MCP Apps)
        OpenProgramCreatorTool::class,
        OpenCohortCreatorTool::class,
    ];

    /**
     * Ressources documentaires et interfaces interactives (MCP Apps).
     */
    protected array $resources = [
        SaeProfileResource::class,
        ActiveProgramsResource::class,
        ProgramCreatorAppResource::class,
        CohortCreatorAppResource::class,
    ];

    /**
     * Modèles de prompts guidés.
     */
    protected array $prompts = [
        EvaluateApplicationPrompt::class,
        SummarizeCohortPrompt::class,
    ];
}
