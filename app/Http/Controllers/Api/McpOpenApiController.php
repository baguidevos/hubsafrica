<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Tool;
use Throwable;

class McpOpenApiController extends Controller
{
    /**
     * Exécute un outil MCP interne et transforme sa réponse en JsonResponse.
     */
    protected function executeTool(Tool $tool, HttpRequest $httpRequest, array $extraArguments = []): JsonResponse
    {
        $arguments = array_merge($httpRequest->all(), $extraArguments);
        $mcpRequest = new McpRequest($arguments);

        try {
            $response = $tool->handle($mcpRequest);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Les paramètres fournis sont invalides.',
                'details' => $e->errors(),
            ], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'MCP_INTERNAL_ERROR',
                'message' => 'Une erreur technique est survenue lors du traitement de votre demande.',
            ], 500);
        }

        if ($response instanceof ResponseFactory) {
            $structured = $response->getStructuredContent();
            if ($structured !== null) {
                return response()->json($structured);
            }

            $first = $response->responses()->first();
            if ($first instanceof Response) {
                return response()->json(['message' => (string) $first->content()]);
            }

            return response()->json(['success' => true]);
        }

        if ($response instanceof Response) {
            $content = $response->content();
            $text = method_exists($content, 'text') ? $content->text() : (string) $content;
            $decoded = json_decode($text, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return response()->json($decoded);
            }

            return response()->json(['message' => $text]);
        }

        return response()->json(is_array($response) ? $response : ['data' => $response]);
    }

    // --- Endpoints OpenAPI / REST ---

    public function getMetrics(HttpRequest $request, GetSaeMetricsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function searchApplications(HttpRequest $request, SearchApplicationsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function getApplication(HttpRequest $request, int $id, GetApplicationDetailsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['application_id' => $id]);
    }

    public function updateApplicationStatus(HttpRequest $request, int $id, UpdateApplicationStatusTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['application_id' => $id]);
    }

    public function scoreApplication(HttpRequest $request, int $id, ScoreApplicationTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['application_id' => $id]);
    }

    public function listProjects(HttpRequest $request, ListProjectsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function getProject(HttpRequest $request, int $id, GetProjectDetailsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['project_id' => $id]);
    }

    public function updateProjectLevel(HttpRequest $request, int $id, UpdateProjectLevelTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['project_id' => $id]);
    }

    public function listDeliverables(HttpRequest $request, int $id, ListProjectDeliverablesTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['project_id' => $id]);
    }

    public function listPrograms(HttpRequest $request, ListProgramsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function createProgram(HttpRequest $request, CreateProgramTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function listCohorts(HttpRequest $request, ListCohortsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function createCohort(HttpRequest $request, CreateCohortTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function updateCohortStatus(HttpRequest $request, int $id, UpdateCohortStatusTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['cohort_id' => $id]);
    }

    public function getCohortSummary(HttpRequest $request, int $id, GetCohortSummaryTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request, ['cohort_id' => $id]);
    }

    public function listCoaches(HttpRequest $request, ListCoachesTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function assignCoach(HttpRequest $request, AssignCoachToProjectTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function listCoachingSessions(HttpRequest $request, ListCoachingSessionsTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    public function logCoachingSession(HttpRequest $request, LogCoachingSessionTool $tool): JsonResponse
    {
        return $this->executeTool($tool, $request);
    }

    /**
     * Génère la spécification OpenAPI 3.0 officielle pour ChatGPT Actions.
     */
    public function schema(HttpRequest $request): JsonResponse
    {
        $serverUrl = $request->schemeAndHttpHost();

        $schema = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Hubs Africa SAE API (MCP Bridge)',
                'description' => 'API de pilotage opérationnel pour les Structures d\'Accompagnement à l\'Entrepreneuriat (SAE) sur Hubs Africa. Permet à ChatGPT d\'accéder aux candidatures, projets, programmes et coaching en toute sécurité.',
                'version' => '1.0.0',
            ],
            'servers' => [
                [
                    'url' => $serverUrl,
                    'description' => 'Serveur Hubs Africa Actif',
                ],
            ],
            'components' => [
                'securitySchemes' => [
                    'BearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'ha_mcp_live_...',
                        'description' => 'Saisir la clé API MCP générée depuis le portail SAE Hubs Africa.',
                    ],
                ],
            ],
            'security' => [
                ['BearerAuth' => []],
            ],
            'paths' => [
                '/api/mcp/v1/metrics' => [
                    'get' => [
                        'summary' => 'Statistiques et métriques de la SAE',
                        'operationId' => 'getSaeMetrics',
                        'responses' => ['200' => ['description' => 'Statistiques globales']],
                    ],
                ],
                '/api/mcp/v1/applications' => [
                    'get' => [
                        'summary' => 'Rechercher des candidatures',
                        'operationId' => 'searchApplications',
                        'parameters' => [
                            ['name' => 'query', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'cohort_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['BROUILLON', 'SOUMISE', 'EN_REVUE', 'ACCEPTEE', 'REFUSEE', 'EN_ATTENTE', 'TOUS']]],
                            ['name' => 'limit', 'in' => 'query', 'schema' => ['type' => 'integer', 'default' => 20]],
                        ],
                        'responses' => ['200' => ['description' => 'Liste des candidatures']],
                    ],
                ],
                '/api/mcp/v1/applications/{id}' => [
                    'get' => [
                        'summary' => 'Consulter une candidature en détail',
                        'operationId' => 'getApplicationDetails',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Dossier complet de la candidature']],
                    ],
                ],
                '/api/mcp/v1/applications/{id}/status' => [
                    'post' => [
                        'summary' => 'Mettre à jour le statut d\'une candidature',
                        'operationId' => 'updateApplicationStatus',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['status'],
                                        'properties' => [
                                            'status' => ['type' => 'string', 'enum' => ['BROUILLON', 'SOUMISE', 'EN_REVUE', 'ACCEPTEE', 'REFUSEE', 'EN_ATTENTE']],
                                            'decision_note' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Statut mis à jour']],
                    ],
                ],
                '/api/mcp/v1/applications/{id}/score' => [
                    'post' => [
                        'summary' => 'Noter et évaluer une candidature',
                        'operationId' => 'scoreApplication',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['score'],
                                        'properties' => [
                                            'score' => ['type' => 'integer', 'minimum' => 0, 'maximum' => 100],
                                            'status' => ['type' => 'string', 'enum' => ['ACCEPTEE', 'REFUSEE', 'EN_ATTENTE']],
                                            'jury_comment' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Évaluation enregistrée']],
                    ],
                ],
                '/api/mcp/v1/projects' => [
                    'get' => [
                        'summary' => 'Lister les startups accompagnées',
                        'operationId' => 'listProjects',
                        'parameters' => [
                            ['name' => 'query', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'sector', 'in' => 'query', 'schema' => ['type' => 'string']],
                            ['name' => 'status', 'in' => 'query', 'schema' => ['type' => 'string', 'enum' => ['ACTIF', 'ARCHIVE', 'DIPLOME', 'ABANDON', 'SUSPENDU', 'TOUS']]],
                            ['name' => 'level_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Liste des projets']],
                    ],
                ],
                '/api/mcp/v1/projects/{id}' => [
                    'get' => [
                        'summary' => 'Fiche détaillée d\'un projet',
                        'operationId' => 'getProjectDetails',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Fiche projet complète']],
                    ],
                ],
                '/api/mcp/v1/projects/{id}/level' => [
                    'post' => [
                        'summary' => 'Faire évoluer le niveau de maturité d\'un projet',
                        'operationId' => 'updateProjectLevel',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['level_id'],
                                        'properties' => [
                                            'level_id' => ['type' => 'integer'],
                                            'notes' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Niveau mis à jour']],
                    ],
                ],
                '/api/mcp/v1/projects/{id}/deliverables' => [
                    'get' => [
                        'summary' => 'Lister les livrables d\'un projet',
                        'operationId' => 'listProjectDeliverables',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Livrables de la startup']],
                    ],
                ],
                '/api/mcp/v1/programs' => [
                    'get' => [
                        'summary' => 'Lister les programmes d\'accompagnement',
                        'operationId' => 'listPrograms',
                        'responses' => ['200' => ['description' => 'Liste des programmes']],
                    ],
                    'post' => [
                        'summary' => 'Créer un programme d\'accompagnement',
                        'operationId' => 'createProgram',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['name', 'description'],
                                        'properties' => [
                                            'name' => ['type' => 'string'],
                                            'description' => ['type' => 'string'],
                                            'duration' => ['type' => 'string'],
                                            'admission_threshold' => ['type' => 'integer'],
                                            'is_published' => ['type' => 'boolean'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Programme créé']],
                    ],
                ],
                '/api/mcp/v1/cohorts' => [
                    'get' => [
                        'summary' => 'Lister les promotions / cohortes',
                        'operationId' => 'listCohorts',
                        'parameters' => [
                            ['name' => 'program_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Liste des cohortes']],
                    ],
                    'post' => [
                        'summary' => 'Créer une nouvelle cohorte',
                        'operationId' => 'createCohort',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['program_id', 'name'],
                                        'properties' => [
                                            'program_id' => ['type' => 'integer'],
                                            'name' => ['type' => 'string'],
                                            'capacity' => ['type' => 'integer'],
                                            'start_date' => ['type' => 'string'],
                                            'end_date' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Cohorte créée']],
                    ],
                ],
                '/api/mcp/v1/cohorts/{id}/summary' => [
                    'get' => [
                        'summary' => 'Bilan synthétique d\'une cohorte',
                        'operationId' => 'getCohortSummary',
                        'parameters' => [
                            ['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Synthèse de la cohorte']],
                    ],
                ],
                '/api/mcp/v1/coaches' => [
                    'get' => [
                        'summary' => 'Lister les coachs et mentors',
                        'operationId' => 'listCoaches',
                        'responses' => ['200' => ['description' => 'Liste des coachs']],
                    ],
                ],
                '/api/mcp/v1/coaching/assign' => [
                    'post' => [
                        'summary' => 'Affecter un coach à un projet',
                        'operationId' => 'assignCoach',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['coach_id', 'project_id'],
                                        'properties' => [
                                            'coach_id' => ['type' => 'integer'],
                                            'project_id' => ['type' => 'integer'],
                                            'role' => ['type' => 'string'],
                                            'notes' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Coach assigné']],
                    ],
                ],
                '/api/mcp/v1/coaching/sessions' => [
                    'get' => [
                        'summary' => 'Lister les séances de coaching',
                        'operationId' => 'listCoachingSessions',
                        'parameters' => [
                            ['name' => 'project_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'coach_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'Liste des séances']],
                    ],
                    'post' => [
                        'summary' => 'Consigner une séance de coaching',
                        'operationId' => 'logCoachingSession',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['project_id', 'coach_id', 'theme', 'date'],
                                        'properties' => [
                                            'project_id' => ['type' => 'integer'],
                                            'coach_id' => ['type' => 'integer'],
                                            'theme' => ['type' => 'string'],
                                            'date' => ['type' => 'string'],
                                            'duration' => ['type' => 'string'],
                                            'hours_delivered' => ['type' => 'number'],
                                            'report' => ['type' => 'string'],
                                            'objectives' => ['type' => 'string'],
                                            'next_actions' => ['type' => 'string'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => ['200' => ['description' => 'Séance enregistrée']],
                    ],
                ],
            ],
        ];

        return response()->json($schema, 200, [
            'Access-Control-Allow-Origin' => '*',
            'Content-Type' => 'application/json',
        ]);
    }
}
