<?php

use App\Filament\Admin\Pages\SaeAiSettingsPage;
use App\Filament\Sae\Pages\AiCopilotPage;
use App\Filament\Sae\Pages\McpIntegrationPage;
use App\Mcp\Resources\CohortCreatorAppResource;
use App\Mcp\Resources\ProgramCreatorAppResource;
use App\Mcp\Servers\SaeMcpServer;
use App\Mcp\Tools\Applications\SearchApplicationsTool;
use App\Mcp\Tools\Metrics\GetSaeMetricsTool;
use App\Mcp\Tools\Programs\CreateProgramTool;
use App\Mcp\Tools\Projects\ListProjectsTool;
use App\Mcp\Tools\Projects\UpdateProjectLevelTool;
use App\Mcp\Tools\Ui\OpenProgramCreatorTool;
use App\Models\McpApiKey;
use App\Models\Sae;
use App\Models\User;
use App\Support\SaeAiFeatures;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Mcp\Server\Transport\FakeTransporter;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('sae ai features are disabled by default and respect master and category guards', function () {
    $defaults = SaeAiFeatures::defaults();

    expect($defaults['ai_enabled'])->toBeFalse()
        ->and($defaults['copilot_enabled'])->toBeFalse()
        ->and($defaults['mcp_keys_enabled'])->toBeFalse()
        ->and($defaults['mcp_server_enabled'])->toBeFalse()
        ->and(SaeAiFeatures::isEnabled($defaults, 'copilot_enabled'))->toBeFalse()
        ->and(SaeAiFeatures::isToolAllowed($defaults, GetSaeMetricsTool::class))->toBeFalse();

    $readOnly = SaeAiFeatures::readOnlyEnabled();

    expect(SaeAiFeatures::isToolAllowed($readOnly, GetSaeMetricsTool::class))->toBeTrue()
        ->and(SaeAiFeatures::isToolAllowed($readOnly, ListProjectsTool::class))->toBeTrue()
        ->and(SaeAiFeatures::isToolAllowed($readOnly, CreateProgramTool::class))->toBeFalse()
        ->and(SaeAiFeatures::isToolAllowed($readOnly, UpdateProjectLevelTool::class))->toBeFalse();

    $custom = SaeAiFeatures::allEnabled();
    $custom['mcp_tools']['applications'] = false;

    expect(SaeAiFeatures::isToolAllowed($custom, SearchApplicationsTool::class))->toBeFalse()
        ->and(SaeAiFeatures::isToolAllowed($custom, ListProjectsTool::class))->toBeTrue();
});

test('sae with default disabled ai features cannot access copilot, mcp keys page, or mcp api', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Sans IA',
        'slug' => 'sae-sans-ia-'.uniqid(),
    ]);
    $tokenResult = McpApiKey::createToken($sae, $owner, 'Clé bloquée');

    $this->actingAs($owner);
    Filament::setTenant($sae);

    expect(AiCopilotPage::canAccess())->toBeFalse()
        ->and(AiCopilotPage::shouldRegisterNavigation())->toBeFalse()
        ->and(McpIntegrationPage::canAccess())->toBeFalse()
        ->and(McpIntegrationPage::shouldRegisterNavigation())->toBeFalse();

    $this->withHeaders([
        'Authorization' => 'Bearer '.$tokenResult['plainTextToken'],
    ])->getJson('/api/mcp/v1/metrics')
        ->assertForbidden()
        ->assertJsonPath('error.code', -32003);
});

test('mcp server filters tools and generative ui resources according to sae ai configuration', function () {
    $owner = User::factory()->create();
    $features = SaeAiFeatures::readOnlyEnabled();
    $features['mcp_tools']['generative_ui'] = false;

    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Lecture Seule',
        'slug' => 'sae-ro-'.uniqid(),
        'ai_features' => $features,
    ]);

    app()->instance('current_sae', $sae);
    app()->instance(Sae::class, $sae);

    $server = new SaeMcpServer(new FakeTransporter);
    $reflection = new ReflectionClass($server);
    $reflection->getMethod('boot')->invoke($server);

    $tools = $reflection->getProperty('tools')->getValue($server);
    $resources = $reflection->getProperty('resources')->getValue($server);

    expect($tools)->toContain(GetSaeMetricsTool::class)
        ->and($tools)->toContain(ListProjectsTool::class)
        ->and($tools)->not->toContain(CreateProgramTool::class)
        ->and($tools)->not->toContain(OpenProgramCreatorTool::class)
        ->and($resources)->not->toContain(ProgramCreatorAppResource::class)
        ->and($resources)->not->toContain(CohortCreatorAppResource::class);
});

test('admin can configure granular ai features per sae from sae ai settings page', function () {
    $admin = User::factory()->create();
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'Incubateur Gouvernance',
        'slug' => 'incubateur-gov-'.uniqid(),
    ]);

    $this->actingAs($admin);

    Livewire::test(SaeAiSettingsPage::class)
        ->assertSee('Incubateur Gouvernance')
        ->call('selectSae', $sae->id)
        ->call('applyPreset', 'readonly')
        ->set('features.mcp_tools.coaching', false)
        ->call('save')
        ->assertNotified('Configuration IA enregistrée');

    $sae->refresh();

    expect($sae->isAiFeatureEnabled('ai_enabled'))->toBeTrue()
        ->and($sae->isAiFeatureEnabled('copilot_enabled'))->toBeTrue()
        ->and($sae->isAiFeatureEnabled('mcp_tools.write_actions'))->toBeFalse()
        ->and($sae->isAiFeatureEnabled('mcp_tools.coaching'))->toBeFalse()
        ->and($sae->isAiFeatureEnabled('mcp_tools.projects'))->toBeTrue();
});
