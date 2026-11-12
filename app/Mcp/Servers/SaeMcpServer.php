<?php

namespace App\Mcp\Servers;

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
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Hubs Africa SAE Server')]
#[Version('1.0.0')]
#[Instructions('Ce serveur MCP permet à l\'agent IA de piloter les opérations de la structure d\'accompagnement (SAE) : gestion des programmes, sélection et notation des candidatures, suivi des startups incubées, affectation des coachs et reporting d\'impact. Chaque action d\'écriture est tracée dans les journaux d\'audit de la SAE.')]
class SaeMcpServer extends Server
{
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
    ];

    /**
     * Ressources documentaires en lecture seule.
     */
    protected array $resources = [
        SaeProfileResource::class,
        ActiveProgramsResource::class,
    ];

    /**
     * Modèles de prompts guidés.
     */
    protected array $prompts = [
        EvaluateApplicationPrompt::class,
        SummarizeCohortPrompt::class,
    ];
}
