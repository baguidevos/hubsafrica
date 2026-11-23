<?php

namespace App\Support;

use App\Mcp\Tools\Applications\EvaluateApplicationByGridTool;
use App\Mcp\Tools\Applications\ExportApplicationEvaluationsSheetTool;
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
use App\Mcp\Tools\Opportunities\ListOpportunitiesTool;
use App\Mcp\Tools\Opportunities\PublishOpportunityTool;
use App\Mcp\Tools\Programs\ConfigureProgramSelectionGridTool;
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
use Illuminate\Support\Arr;

final class SaeAiFeatures
{
    /**
     * Configuration IA par défaut d'une SAE (désactivée tant que non activée par l'administrateur).
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'ai_enabled' => false,
            'copilot_enabled' => false,
            'mcp_keys_enabled' => false,
            'mcp_server_enabled' => false,
            'mcp_tools' => [
                'applications' => true,
                'projects' => true,
                'programs' => true,
                'opportunities' => true,
                'coaching' => true,
                'metrics' => true,
                'generative_ui' => true,
                'write_actions' => true,
            ],
        ];
    }

    /**
     * Configuration avec tous les modules et outils IA activés.
     *
     * @return array<string, mixed>
     */
    public static function allEnabled(): array
    {
        return [
            'ai_enabled' => true,
            'copilot_enabled' => true,
            'mcp_keys_enabled' => true,
            'mcp_server_enabled' => true,
            'mcp_tools' => [
                'applications' => true,
                'projects' => true,
                'programs' => true,
                'opportunities' => true,
                'coaching' => true,
                'metrics' => true,
                'generative_ui' => true,
                'write_actions' => true,
            ],
        ];
    }

    /**
     * Configuration en lecture seule IA (consultation sans modification).
     *
     * @return array<string, mixed>
     */
    public static function readOnlyEnabled(): array
    {
        $config = self::allEnabled();
        $config['mcp_tools']['write_actions'] = false;

        return $config;
    }

    /**
     * Fusionne les paramètres stockés avec les valeurs par défaut.
     *
     * @param  array<string, mixed>|null  $stored
     * @return array<string, mixed>
     */
    public static function resolve(?array $stored): array
    {
        return array_replace_recursive(self::defaults(), $stored ?? []);
    }

    /**
     * Détermine si une fonctionnalité IA donnée est active pour une SAE.
     *
     * @param  array<string, mixed>|null  $stored
     */
    public static function isEnabled(?array $stored, string $feature): bool
    {
        $resolved = self::resolve($stored);

        if (! ($resolved['ai_enabled'] ?? false)) {
            return false;
        }

        if ($feature === 'ai_enabled') {
            return true;
        }

        if (str_starts_with($feature, 'mcp_tools.') && ! ($resolved['mcp_server_enabled'] ?? false)) {
            return false;
        }

        return (bool) Arr::get($resolved, $feature, false);
    }

    /**
     * Vérifie si un outil MCP spécifique est autorisé pour cette configuration.
     *
     * @param  array<string, mixed>|null  $stored
     */
    public static function isToolAllowed(?array $stored, string $toolClass): bool
    {
        if (! self::isEnabled($stored, 'mcp_server_enabled')) {
            return false;
        }

        $descriptor = self::toolRegistry()[$toolClass] ?? null;

        if ($descriptor === null) {
            return true;
        }

        if (! self::isEnabled($stored, "mcp_tools.{$descriptor['category']}")) {
            return false;
        }

        if ($descriptor['write'] && ! self::isEnabled($stored, 'mcp_tools.write_actions')) {
            return false;
        }

        return true;
    }

    /**
     * Libellés métier des catégories d'outils pour l'interface d'administration.
     *
     * @return array<string, string>
     */
    public static function categoryLabels(): array
    {
        return [
            'applications' => 'Candidatures & Sélection',
            'projects' => 'Projets & Startups',
            'programs' => 'Programmes & Cohortes',
            'opportunities' => 'Opportunités & Appels à projets',
            'coaching' => 'Coaching & Mentors',
            'metrics' => 'Indicateurs & Synthèses',
            'generative_ui' => 'Interfaces Génératives (MCP Apps)',
        ];
    }

    /**
     * Associe chaque outil MCP à sa catégorie et indique s'il modifie des données.
     *
     * @return array<class-string, array{category: string, write: bool}>
     */
    public static function toolRegistry(): array
    {
        return [
            // Candidatures
            SearchApplicationsTool::class => ['category' => 'applications', 'write' => false],
            GetApplicationDetailsTool::class => ['category' => 'applications', 'write' => false],
            EvaluateApplicationByGridTool::class => ['category' => 'applications', 'write' => true],
            ExportApplicationEvaluationsSheetTool::class => ['category' => 'applications', 'write' => false],
            UpdateApplicationStatusTool::class => ['category' => 'applications', 'write' => true],
            ScoreApplicationTool::class => ['category' => 'applications', 'write' => true],

            // Projets
            ListProjectsTool::class => ['category' => 'projects', 'write' => false],
            GetProjectDetailsTool::class => ['category' => 'projects', 'write' => false],
            ListProjectDeliverablesTool::class => ['category' => 'projects', 'write' => false],
            UpdateProjectLevelTool::class => ['category' => 'projects', 'write' => true],

            // Programmes & Cohortes
            ListProgramsTool::class => ['category' => 'programs', 'write' => false],
            CreateProgramTool::class => ['category' => 'programs', 'write' => true],
            ConfigureProgramSelectionGridTool::class => ['category' => 'programs', 'write' => true],
            ListCohortsTool::class => ['category' => 'programs', 'write' => false],
            CreateCohortTool::class => ['category' => 'programs', 'write' => true],
            UpdateCohortStatusTool::class => ['category' => 'programs', 'write' => true],

            // Opportunités
            ListOpportunitiesTool::class => ['category' => 'opportunities', 'write' => false],
            PublishOpportunityTool::class => ['category' => 'opportunities', 'write' => true],

            // Coaching
            ListCoachesTool::class => ['category' => 'coaching', 'write' => false],
            ListCoachingSessionsTool::class => ['category' => 'coaching', 'write' => false],
            AssignCoachToProjectTool::class => ['category' => 'coaching', 'write' => true],
            LogCoachingSessionTool::class => ['category' => 'coaching', 'write' => true],

            // Métriques
            GetSaeMetricsTool::class => ['category' => 'metrics', 'write' => false],
            GetCohortSummaryTool::class => ['category' => 'metrics', 'write' => false],

            // Interfaces Génératives
            OpenProgramCreatorTool::class => ['category' => 'generative_ui', 'write' => false],
            OpenCohortCreatorTool::class => ['category' => 'generative_ui', 'write' => false],
        ];
    }
}
