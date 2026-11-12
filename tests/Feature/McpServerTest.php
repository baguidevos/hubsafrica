<?php

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\ApplicationStatus;
use App\Filament\Sae\Pages\AiCopilotPage;
use App\Filament\Sae\Pages\McpIntegrationPage;
use App\Mcp\Tools\Metrics\GetSaeMetricsTool;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\AuditLog;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\McpApiKey;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('mcp endpoints reject requests without a valid api key', function () {
    $response = $this->getJson('/api/mcp/v1/metrics');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', -32000);
});

test('mcp endpoints reject requests with an invalid api key', function () {
    $response = $this->withHeader('Authorization', 'Bearer ha_mcp_live_invalidtoken123')
        ->getJson('/api/mcp/v1/metrics');

    $response->assertStatus(401)
        ->assertJsonPath('error.code', -32001);
});

test('openapi specification endpoint is public and valid', function () {
    $response = $this->getJson('/api/mcp/openapi.json');

    $response->assertStatus(200)
        ->assertJsonPath('openapi', '3.0.3')
        ->assertJsonPath('info.title', 'Hubs Africa SAE API (MCP Bridge)');

    expect($response->json('paths'))->toHaveKey('/api/mcp/v1/metrics')
        ->toHaveKey('/api/mcp/v1/applications')
        ->toHaveKey('/api/mcp/v1/projects');
});

test('mcp api authenticates and strictly isolates data per sae', function () {
    $userA = User::factory()->create();
    $saeA = Sae::create([
        'owner_id' => $userA->id,
        'name' => 'Incubateur Alpha',
        'slug' => 'incubateur-alpha-'.uniqid(),
    ]);
    $tokenResultA = McpApiKey::createToken($saeA, $userA, 'Token Alpha');

    $entUserA = User::factory()->create();
    $entrepreneurA = Entrepreneur::create([
        'sae_id' => $saeA->id,
        'user_id' => $entUserA->id,
        'first_name' => 'Amadou',
        'last_name' => 'Diallo',
        'email' => $entUserA->email,
        'status' => 'ACTIVE',
    ]);

    $userB = User::factory()->create();
    $saeB = Sae::create([
        'owner_id' => $userB->id,
        'name' => 'Incubateur Beta',
        'slug' => 'incubateur-beta-'.uniqid(),
    ]);

    $entUserB = User::factory()->create();
    $entrepreneurB = Entrepreneur::create([
        'sae_id' => $saeB->id,
        'user_id' => $entUserB->id,
        'first_name' => 'Fatou',
        'last_name' => 'Ndiaye',
        'email' => $entUserB->email,
        'status' => 'ACTIVE',
    ]);

    $programA = Program::create([
        'sae_id' => $saeA->id,
        'name' => 'Programme Alpha Exclusive',
        'description' => 'Programme pour Alpha',
    ]);

    $programB = Program::create([
        'sae_id' => $saeB->id,
        'name' => 'Programme Beta Confidential',
        'description' => 'Programme pour Beta',
    ]);

    $projectA = Project::create([
        'sae_id' => $saeA->id,
        'entrepreneur_id' => $entrepreneurA->id,
        'name' => 'Startup Alpha Tech',
        'sector' => 'AgriTech',
    ]);

    $projectB = Project::create([
        'sae_id' => $saeB->id,
        'entrepreneur_id' => $entrepreneurB->id,
        'name' => 'Startup Beta Tech',
        'sector' => 'FinTech',
    ]);

    // Test des métriques pour SAE A
    $metricsResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResultA['plainTextToken'])
        ->getJson('/api/mcp/v1/metrics');

    $metricsResponse->assertStatus(200)
        ->assertJsonPath('sae.name', 'Incubateur Alpha');

    // Test de la liste des projets pour SAE A : seul le projet de SAE A doit apparaître
    $projectsResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResultA['plainTextToken'])
        ->getJson('/api/mcp/v1/projects');

    $projectsResponse->assertStatus(200);
    $projectNames = collect($projectsResponse->json('projects'))->pluck('name')->all();

    expect($projectNames)->toContain('Startup Alpha Tech')
        ->not->toContain('Startup Beta Tech');

    // Test de la liste des programmes pour SAE A : seul le programme de SAE A doit apparaître
    $programsResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResultA['plainTextToken'])
        ->getJson('/api/mcp/v1/programs');

    $programsResponse->assertStatus(200);
    $programNames = collect($programsResponse->json('programs'))->pluck('name')->all();

    expect($programNames)->toContain('Programme Alpha Exclusive')
        ->not->toContain('Programme Beta Confidential');
});

test('mcp action logs audit trail in audit_logs table', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Test Audit',
        'slug' => 'incubateur-test-audit-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Claude Audit Bot');

    $entrepreneurUser = User::factory()->create();
    $entrepreneur = Entrepreneur::create([
        'user_id' => $entrepreneurUser->id,
        'sae_id' => $sae->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => $entrepreneurUser->email,
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Test',
        'description' => 'Desc',
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Startup Evaluée',
    ]);

    $application = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'code' => 'APP-TEST-'.uniqid(),
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    $updateResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson("/api/mcp/v1/applications/{$application->id}/status", [
            'status' => 'ACCEPTEE',
            'decision_note' => 'Admis suite à évaluation positive du copilote IA.',
        ]);

    $updateResponse->assertStatus(200);

    $application->refresh();
    expect($application->status)->toBe(ApplicationStatus::ACCEPTEE);

    // Vérification de la création du log d'audit
    $auditLog = AuditLog::where('sae_id', $sae->id)
        ->where('action', 'UPDATE_STATUS')
        ->where('entity_id', (string) $application->id)
        ->latest()
        ->first();

    expect($auditLog)->not->toBeNull();
    expect($auditLog->user_name)->toContain('Claude Audit Bot');
});

test('native mcp server endpoint responds to json-rpc tools/list with all 17 registered tools', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur JSON-RPC',
        'slug' => 'incubateur-jsonrpc-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Claude JSON-RPC');

    $response = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/list',
            'params' => [
                'per_page' => 50,
            ],
        ]);

    $response->assertStatus(200);
    $tools = collect($response->json('result.tools'))->pluck('name')->all();

    expect($tools)->toContain(
        'search_applications',
        'get_application_details',
        'update_application_status',
        'score_application',
        'list_projects',
        'get_project_details',
        'update_project_level',
        'list_project_deliverables',
        'list_programs',
        'create_program',
        'list_cohorts',
        'create_cohort',
        'update_cohort_status',
        'list_coaches',
        'assign_coach_to_project',
        'list_coaching_sessions',
        'log_coaching_session',
        'get_sae_metrics',
        'get_cohort_summary',
    );
});

test('native mcp server endpoint executes tools/call for get_sae_metrics', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur MCP Tool Call',
        'slug' => 'incubateur-mcp-tool-call-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Claude Call Bot');

    $response = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 2,
            'method' => 'tools/call',
            'params' => [
                'name' => 'get_sae_metrics',
                'arguments' => (object) [],
            ],
        ]);

    $response->assertStatus(200)
        ->assertJsonPath('result.content.0.type', 'text');

    $text = $response->json('result.content.0.text');
    $data = json_decode($text, true);

    expect($data['sae']['name'])->toBe('Incubateur MCP Tool Call');
});

test('native mcp server endpoint responds to prompts/list and resources/list', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Resources',
        'slug' => 'incubateur-resources-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Claude Discovery Bot');

    // Prompts
    $promptsResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 3,
            'method' => 'prompts/list',
            'params' => (object) [],
        ]);

    $promptsResponse->assertStatus(200);
    $promptNames = collect($promptsResponse->json('result.prompts'))->pluck('name')->all();

    expect($promptNames)->toContain('evaluate_application', 'summarize_cohort');

    // Resources
    $resourcesResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 4,
            'method' => 'resources/list',
            'params' => (object) [],
        ]);

    $resourcesResponse->assertStatus(200);
    $resourceUris = collect($resourcesResponse->json('result.resources'))->pluck('uri')->all();

    expect($resourceUris)->toContain('hubafrica://sae/profile', 'hubafrica://sae/programs/active');
});

test('cohort tools allow creating and updating cohort status with valid enum values', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Cohortes',
        'slug' => 'incubateur-cohortes-'.uniqid(),
    ]);
    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Accélération 2026',
        'description' => 'Programme test',
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Cohorte Bot');

    // 1. Create cohort via tool call
    $createResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 5,
            'method' => 'tools/call',
            'params' => [
                'name' => 'create_cohort',
                'arguments' => [
                    'program_id' => $program->id,
                    'name' => 'Promotion Printemps 2026',
                    'capacity' => 25,
                ],
            ],
        ]);

    $createResponse->assertStatus(200);
    $this->assertDatabaseHas('cohorts', [
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion Printemps 2026',
        'status' => 'OUVERTE',
    ]);

    $cohort = Cohort::where('name', 'Promotion Printemps 2026')->first();
    expect($cohort)->not->toBeNull();
    expect($cohort->number)->toBe(1);

    // 2. Update status to EN_COURS
    $updateResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 6,
            'method' => 'tools/call',
            'params' => [
                'name' => 'update_cohort_status',
                'arguments' => [
                    'cohort_id' => $cohort->id,
                    'status' => 'EN_COURS',
                ],
            ],
        ]);

    $updateResponse->assertStatus(200);
    $cohort->refresh();
    expect($cohort->status->value)->toBe('EN_COURS');
});

test('mcp integration filament page loads successfully for sae tenant and displays active keys', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Teranga Africa SAE',
        'slug' => 'teranga-africa-'.uniqid(),
    ]);
    $user->saes()->syncWithoutDetaching([$sae->id => ['role' => 'RESPONSABLE']]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    McpApiKey::createToken($sae, $user, 'Clé Test Filament');

    Livewire::test(McpIntegrationPage::class)
        ->assertSuccessful()
        ->assertSee('Teranga Africa SAE')
        ->assertSee('Clé Test Filament')
        ->assertSee('Serveur MCP');
});

test('get_application_details tool loads application with evaluations cleanly', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Eval',
        'slug' => 'incubateur-eval-'.uniqid(),
    ]);
    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Test',
        'description' => 'Desc',
    ]);
    $entUser = User::factory()->create();
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $entUser->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => $entUser->email,
        'status' => 'ACTIVE',
    ]);
    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'ScoopScore',
        'sector' => 'Tech',
    ]);
    $application = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => $project->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    ApplicationEvaluation::create([
        'sae_id' => $sae->id,
        'application_id' => $application->id,
        'evaluator_id' => $user->id,
        'comment' => 'Très bon projet',
        'total_score' => 85,
        'status' => ApplicationEvaluationStatus::SOUMIS,
    ]);

    $tokenResult = McpApiKey::createToken($sae, $user, 'Eval Bot');

    $response = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->getJson("/api/mcp/v1/applications/{$application->id}");

    $response->assertStatus(200)
        ->assertJsonPath('project.name', 'ScoopScore')
        ->assertJsonPath('evaluations.0.evaluator_name', $user->name);
});

test('mcp server hides explicit internal exceptions even when app debug is enabled', function () {
    config(['app.debug' => true]);

    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Secure Error',
        'slug' => 'incubateur-secure-error-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'Secure Bot');

    // Simuler une exception interne dans un outil via un mock
    $this->mock(GetSaeMetricsTool::class, function ($mock) {
        $mock->shouldReceive('handle')->andThrow(new RuntimeException('Call to undefined relationship [secret] on model [App\Models\SecretModel]'));
    });

    $response = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->getJson('/api/mcp/v1/metrics');

    $response->assertStatus(500)
        ->assertJsonPath('error', 'MCP_INTERNAL_ERROR');

    expect($response->getContent())->not->toContain('SecretModel')
        ->not->toContain('undefined relationship');
});

test('mcp server exposes generative ui tools and renders interactive mcp app resources', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur GenUI',
        'slug' => 'incubateur-genui-'.uniqid(),
    ]);
    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme GenUI 2026',
        'description' => 'Programme test',
    ]);
    $tokenResult = McpApiKey::createToken($sae, $user, 'GenUI Bot');

    // 1. tools/list doit contenir open_program_creator avec _meta.ui.resourceUri
    $toolsResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 10,
            'method' => 'tools/list',
            'params' => (object) [],
        ]);

    $toolsResponse->assertStatus(200);
    $tools = collect($toolsResponse->json('result.tools'));
    $programCreatorTool = $tools->firstWhere('name', 'open_program_creator');

    expect($programCreatorTool)->not->toBeNull();
    expect($programCreatorTool['_meta']['ui']['resourceUri'] ?? null)->toBe('ui://hubafrica/program-creator');

    // 2. resources/read sur ui://hubafrica/program-creator retourne la carte HTML interactive
    $programUiResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 11,
            'method' => 'resources/read',
            'params' => [
                'uri' => 'ui://hubafrica/program-creator',
            ],
        ]);

    $programUiResponse->assertStatus(200)
        ->assertJsonPath('result.contents.0.mimeType', 'text/html;profile=mcp-app');

    expect($programUiResponse->json('result.contents.0.text'))
        ->toContain('Nouveau Programme')
        ->toContain('Incubateur GenUI')
        ->toContain('create_program');

    // 3. resources/read sur ui://hubafrica/cohort-creator retourne la carte avec le programme existant
    $cohortUiResponse = $this->withHeader('Authorization', 'Bearer '.$tokenResult['plainTextToken'])
        ->postJson('/mcp/sae', [
            'jsonrpc' => '2.0',
            'id' => 12,
            'method' => 'resources/read',
            'params' => [
                'uri' => 'ui://hubafrica/cohort-creator',
            ],
        ]);

    $cohortUiResponse->assertStatus(200)
        ->assertJsonPath('result.contents.0.mimeType', 'text/html;profile=mcp-app');

    expect($cohortUiResponse->json('result.contents.0.text'))
        ->toContain('Nouvelle Cohorte')
        ->toContain('Programme GenUI 2026')
        ->toContain('create_cohort');
});

test('filament ai copilot page renders interactive widgets and creates programs', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Copilot GenUI',
        'slug' => 'sae-copilot-genui-'.uniqid(),
    ]);
    $user->saes()->syncWithoutDetaching([$sae->id => ['role' => 'RESPONSABLE']]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    Livewire::test(AiCopilotPage::class)
        ->assertSuccessful()
        ->call('submitPrompt', 'Je veux créer un programme')
        ->assertSet('activeWidget', 'program_creator')
        ->set('programName', 'Programme Copilot 2026')
        ->set('programDescription', 'Créé via carte interactive Filament')
        ->call('createProgram')
        ->assertSet('activeWidget', 'metrics_dashboard');

    $this->assertDatabaseHas('programs', [
        'sae_id' => $sae->id,
        'name' => 'Programme Copilot 2026',
    ]);
});
