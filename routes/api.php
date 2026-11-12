<?php

use App\Http\Controllers\Api\McpOAuthController;
use App\Http\Controllers\Api\McpOpenApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Hubs Africa
|--------------------------------------------------------------------------
*/

// Spécification OpenAPI publique pour importation dans les Custom GPTs OpenAI
Route::get('/mcp/openapi.json', [McpOpenApiController::class, 'schema'])->name('api.mcp.openapi.schema');

// Endpoints MCP OpenAPI / REST — protégés par Bearer Token SAE
Route::middleware(['auth.mcp'])->prefix('mcp/v1')->group(function () {
    // Métriques & Impact
    Route::get('/metrics', [McpOpenApiController::class, 'getMetrics'])->name('api.mcp.metrics');

    // Candidatures
    Route::get('/applications', [McpOpenApiController::class, 'searchApplications'])->name('api.mcp.applications.search');
    Route::get('/applications/{id}', [McpOpenApiController::class, 'getApplication'])->name('api.mcp.applications.show');
    Route::post('/applications/{id}/status', [McpOpenApiController::class, 'updateApplicationStatus'])->name('api.mcp.applications.status');
    Route::post('/applications/{id}/score', [McpOpenApiController::class, 'scoreApplication'])->name('api.mcp.applications.score');

    // Projets / Startups
    Route::get('/projects', [McpOpenApiController::class, 'listProjects'])->name('api.mcp.projects.list');
    Route::get('/projects/{id}', [McpOpenApiController::class, 'getProject'])->name('api.mcp.projects.show');
    Route::post('/projects/{id}/level', [McpOpenApiController::class, 'updateProjectLevel'])->name('api.mcp.projects.level');
    Route::get('/projects/{id}/deliverables', [McpOpenApiController::class, 'listDeliverables'])->name('api.mcp.projects.deliverables');

    // Programmes & Cohortes
    Route::get('/programs', [McpOpenApiController::class, 'listPrograms'])->name('api.mcp.programs.list');
    Route::post('/programs', [McpOpenApiController::class, 'createProgram'])->name('api.mcp.programs.create');
    Route::get('/cohorts', [McpOpenApiController::class, 'listCohorts'])->name('api.mcp.cohorts.list');
    Route::post('/cohorts', [McpOpenApiController::class, 'createCohort'])->name('api.mcp.cohorts.create');
    Route::post('/cohorts/{id}/status', [McpOpenApiController::class, 'updateCohortStatus'])->name('api.mcp.cohorts.status');
    Route::get('/cohorts/{id}/summary', [McpOpenApiController::class, 'getCohortSummary'])->name('api.mcp.cohorts.summary');

    // Coaching
    Route::get('/coaches', [McpOpenApiController::class, 'listCoaches'])->name('api.mcp.coaches.list');
    Route::post('/coaching/assign', [McpOpenApiController::class, 'assignCoach'])->name('api.mcp.coaching.assign');
    Route::get('/coaching/sessions', [McpOpenApiController::class, 'listCoachingSessions'])->name('api.mcp.coaching.sessions.list');
    Route::post('/coaching/sessions', [McpOpenApiController::class, 'logCoachingSession'])->name('api.mcp.coaching.sessions.create');
});

// OAuth 2.0 Connecteurs IA (endpoints API)
Route::prefix('oauth')->group(function () {
    Route::post('/token', [McpOAuthController::class, 'token'])->name('api.oauth.token');
    Route::middleware(['auth.mcp'])->get('/userinfo', [McpOAuthController::class, 'userInfo'])->name('api.oauth.userinfo');
});
