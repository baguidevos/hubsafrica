<?php

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use App\Enums\AccompanimentToolRequestStatus;
use App\Filament\Coach\Pages\SharedToolsLibraryPage as CoachSharedToolsLibraryPage;
use App\Filament\Sae\Pages\SharedToolsLibraryPage as SaeSharedToolsLibraryPage;
use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolAccessRequest;
use App\Models\Sae;
use App\Models\SaeType;
use App\Models\User;
use App\Support\AccompanimentToolAccessPolicy;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('matchesSaeTypes evaluates universal, matching, non-matching, and empty sae types purely', function () {
    expect(AccompanimentToolAccessPolicy::matchesSaeTypes([], [1, 2]))->toBeTrue();
    expect(AccompanimentToolAccessPolicy::matchesSaeTypes([], []))->toBeTrue();
    expect(AccompanimentToolAccessPolicy::matchesSaeTypes([1, 3], [2, 3]))->toBeTrue();
    expect(AccompanimentToolAccessPolicy::matchesSaeTypes([1, 3], [2, 4]))->toBeFalse();
    expect(AccompanimentToolAccessPolicy::matchesSaeTypes([1, 3], []))->toBeFalse();
});

test('sae and coach panels show network-shared tools matching viewer sae types and universal tools', function () {
    $couveuseType = SaeType::create(['name' => 'Couveuse', 'slug' => 'couveuse', 'is_active' => true]);
    $acceleratorType = SaeType::create(['name' => 'Accélérateur', 'slug' => 'accelerateur', 'is_active' => true]);

    $ownerUser = User::factory()->create();
    $saeA = Sae::create([
        'owner_id' => $ownerUser->id,
        'name' => 'SAE Source',
        'slug' => 'sae-source-'.uniqid(),
    ]);

    $couveuseTool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Kit Couveuse CUBE',
        'slug' => 'kit-couveuse-cube',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::ACCOUNT_REQUIRED,
        'is_published' => true,
        'is_shared_with_network' => true,
    ]);
    $couveuseTool->saeTypes()->attach($couveuseType->id);

    $acceleratorTool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Grille Accélérateur',
        'slug' => 'grille-accelerateur',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => true,
        'is_shared_with_network' => true,
    ]);
    $acceleratorTool->saeTypes()->attach($acceleratorType->id);

    $universalTool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Charte Universelle',
        'slug' => 'charte-universelle',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => true,
        'is_shared_with_network' => true,
    ]);

    $unsharedCouveuseTool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Outil Interne Couveuse',
        'slug' => 'outil-interne-couveuse',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => true,
        'is_shared_with_network' => false,
    ]);
    $unsharedCouveuseTool->saeTypes()->attach($couveuseType->id);

    $unpublishedCouveuseTool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Brouillon Couveuse',
        'slug' => 'brouillon-couveuse',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => false,
        'is_shared_with_network' => true,
    ]);
    $unpublishedCouveuseTool->saeTypes()->attach($couveuseType->id);

    $saeManager = User::factory()->create();
    $saeB = Sae::create([
        'owner_id' => $saeManager->id,
        'name' => 'Couveuse Partenaire',
        'slug' => 'couveuse-partenaire-'.uniqid(),
    ]);
    $saeB->types()->attach($couveuseType->id);

    $coachUser = User::factory()->create();

    $this->actingAs($saeManager);
    Filament::setCurrentPanel('sae');
    Filament::setTenant($saeB);

    Livewire::test(SaeSharedToolsLibraryPage::class)
        ->assertSee('Kit Couveuse CUBE')
        ->assertSee('Charte Universelle')
        ->assertDontSee('Grille Accélérateur')
        ->assertDontSee('Outil Interne Couveuse')
        ->assertDontSee('Brouillon Couveuse')
        ->set('selectedTypeFilter', (string) $acceleratorType->id)
        ->assertSee('Grille Accélérateur')
        ->assertSee('Charte Universelle')
        ->assertDontSee('Kit Couveuse CUBE')
        ->set('selectedTypeFilter', 'all')
        ->assertSee('Kit Couveuse CUBE')
        ->assertSee('Grille Accélérateur')
        ->assertSee('Charte Universelle');

    $this->actingAs($coachUser);
    Filament::setCurrentPanel('coach');
    Filament::setTenant($saeB);

    Livewire::test(CoachSharedToolsLibraryPage::class)
        ->assertSee('Kit Couveuse CUBE')
        ->assertSee('Charte Universelle')
        ->assertDontSee('Grille Accélérateur')
        ->assertDontSee('Outil Interne Couveuse')
        ->assertDontSee('Brouillon Couveuse');
});

test('cross-tenant evaluateAccessFor resolves approved request when another tenant is active', function () {
    $ownerUser = User::factory()->create();
    $saeA = Sae::create([
        'owner_id' => $ownerUser->id,
        'name' => 'SAE Source',
        'slug' => 'sae-source-'.uniqid(),
    ]);

    $viewerUser = User::factory()->create();
    $saeB = Sae::create([
        'owner_id' => $viewerUser->id,
        'name' => 'SAE Lectrice',
        'slug' => 'sae-lectrice-'.uniqid(),
    ]);

    $tool = AccompanimentTool::create([
        'sae_id' => $saeA->id,
        'created_by_id' => $ownerUser->id,
        'title' => 'Outil Sur Demande',
        'slug' => 'outil-sur-demande',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'access_level' => AccompanimentToolAccessLevel::ON_REQUEST,
        'is_published' => true,
        'is_shared_with_network' => true,
    ]);

    AccompanimentToolAccessRequest::create([
        'sae_id' => $saeA->id,
        'accompaniment_tool_id' => $tool->id,
        'user_id' => $viewerUser->id,
        'status' => AccompanimentToolRequestStatus::APPROVED,
    ]);

    $this->actingAs($viewerUser);
    Filament::setCurrentPanel('coach');
    Filament::setTenant($saeB);

    $decision = $tool->evaluateAccessFor($viewerUser);

    expect($decision->canAccess)->toBeTrue()
        ->and($decision->code)->toBe('ACCESS_GRANTED');
});
