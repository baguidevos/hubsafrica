<?php

use App\Enums\Gender;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Support\EntrepreneurCompletionChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

test('evaluate computes profile and project completion purely using text and select fields only', function () {
    $fullProfile = [
        'first_name' => 'Awa',
        'last_name' => 'Diallo',
        'email' => 'awa@example.com',
        'phone' => '+221770000000',
        'gender' => Gender::F,
        'birth_date' => '1995-04-12',
        'country' => 'Sénégal',
        'territory' => 'Dakar',
        'education_level' => 'Master',
        'profession' => 'Ingénieure',
        'professional_status' => 'Entrepreneure',
    ];

    $fullProject = [
        'name' => 'AgriPulse',
        'sector' => 'AgriTech',
        'territory' => 'Dakar',
        'team_size' => 4,
        'target' => 'Coopératives agricoles',
        'market' => 'Afrique de l\'Ouest',
        'value_proposition' => 'Irrigation solaire connectée',
        'problem' => 'Pertes hydriques élevées',
        'solution' => 'Capteurs IoT autonomes',
        'product_service' => 'Kit capteurs + app mobile',
        'pitch' => 'Optimiser l\'eau agricole par IoT',
        'description' => 'Solution complète pour petites exploitations.',
    ];

    $complete = EntrepreneurCompletionChecker::evaluate($fullProfile, $fullProject);
    expect($complete['is_complete'])->toBeTrue()
        ->and($complete['percentage'])->toBe(100)
        ->and($complete['missing_profile_fields'])->toBe([])
        ->and($complete['missing_project_fields'])->toBe([]);

    $missingOneProfile = $fullProfile;
    $missingOneProfile['profession'] = '   ';
    $partialProfile = EntrepreneurCompletionChecker::evaluate($missingOneProfile, $fullProject);
    expect($partialProfile['is_complete'])->toBeFalse()
        ->and($partialProfile['profile_complete'])->toBeFalse()
        ->and($partialProfile['project_complete'])->toBeTrue()
        ->and($partialProfile['missing_profile_fields'])->toBe(['Profession']);

    $missingOneProject = $fullProject;
    $missingOneProject['solution'] = null;
    $partialProject = EntrepreneurCompletionChecker::evaluate($fullProfile, $missingOneProject);
    expect($partialProject['is_complete'])->toBeFalse()
        ->and($partialProject['profile_complete'])->toBeTrue()
        ->and($partialProject['project_complete'])->toBeFalse()
        ->and($partialProject['missing_project_fields'])->toBe(['Solution']);

    $noProject = EntrepreneurCompletionChecker::evaluate($fullProfile, null);
    expect($noProject['is_complete'])->toBeFalse()
        ->and($noProject['has_project'])->toBeFalse()
        ->and($noProject['project_complete'])->toBeFalse();
});

test('portal topbar displays completion banner when incomplete and hides it immediately after saving missing fields', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'Couveuse Test',
        'slug' => 'couveuse-test-'.uniqid(),
    ]);

    $entrepreneurUser = User::factory()->create();
    $entrepreneurUser->saes()->attach($sae->id, [
        'role' => 'ENTREPRENEUR',
        'status' => 'ACTIVE',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $entrepreneurUser->id,
        'first_name' => 'Moussa',
        'last_name' => 'Ndiaye',
        'email' => $entrepreneurUser->email,
        'phone' => '+221771112233',
        'gender' => Gender::M,
        'territory' => 'Dakar',
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'SolarFarm',
        'sector' => 'Énergie',
        'territory' => 'Dakar',
        'team_size' => 2,
    ]);

    $this->actingAs($entrepreneurUser)
        ->get("/portal/{$sae->slug}")
        ->assertOk()
        ->assertSee('Compléter mon profil')
        ->assertSee('Compléter mon projet');

    expect(Cache::has(EntrepreneurCompletionChecker::cacheKey($entrepreneurUser->id, $sae->id)))->toBeTrue();

    // Compléter tous les champs texte/sélection (sans logo) doit invalider le cache automatiquement
    $entrepreneur->update([
        'birth_date' => '1992-05-20',
        'country' => 'Sénégal',
        'education_level' => 'Licence',
        'profession' => 'Développeur',
        'professional_status' => 'Fondateur',
    ]);

    expect(Cache::has(EntrepreneurCompletionChecker::cacheKey($entrepreneurUser->id, $sae->id)))->toBeFalse();

    $project->update([
        'target' => 'PME rurales',
        'market' => 'Sénégal',
        'value_proposition' => 'Énergie fiable à bas coût',
        'problem' => 'Coupures fréquentes',
        'solution' => 'Mini-réseaux solaires',
        'product_service' => 'Abonnement solaire mensuel',
        'pitch' => 'Électrifier les zones rurales',
        'description' => 'Déploiement de kits solaires modulaires.',
    ]);

    $this->actingAs($entrepreneurUser)
        ->get("/portal/{$sae->slug}")
        ->assertOk()
        ->assertDontSee('Compléter mon profil')
        ->assertDontSee('Compléter mon projet');
});
