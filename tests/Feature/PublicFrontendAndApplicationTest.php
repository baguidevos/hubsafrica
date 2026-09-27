<?php

use App\Enums\ApplicationStatus;
use App\Enums\ProjectStatus;
use App\Livewire\Public\ProgramApplicationWizard;
use App\Livewire\Public\ProgrammesCatalog;
use App\Models\Application;
use App\Models\Entrepreneur;
use App\Models\Level;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Livewire\Livewire;

test('public home page loads successfully with institutional branding and metrics', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Hub Sénégal',
        'slug' => 'sae-hub-senegal',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Accélération Tech 2026',
        'status' => 'ACTIF',
        'description' => 'Programme de croissance pour startups africaines.',
    ]);

    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('HUB');
    $response->assertSee('AFRICA');
    $response->assertSee('Startups Accompagnées');
    $response->assertSee('Accélération Tech 2026');
});

test('public programmes catalog page and livewire component render with reactive filtering', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Hub Innovation Dakar',
        'slug' => 'hub-innovation-dakar',
    ]);

    $level = Level::create([
        'sae_id' => $sae->id,
        'name' => 'Maturation MVP',
        'order_index' => 1,
    ]);

    $programA = Program::create([
        'sae_id' => $sae->id,
        'level_id' => $level->id,
        'name' => 'AgriTech Booster',
        'status' => 'ACTIF',
        'capacity' => 15,
        'description' => 'Booster pour solutions agricoles innovantes.',
    ]);

    $programB = Program::create([
        'sae_id' => $sae->id,
        'name' => 'FinTech Scale',
        'status' => 'CLOTURE',
        'capacity' => 20,
        'description' => 'Accélération pour services financiers inclusifs.',
    ]);

    $this->get('/programmes')
        ->assertOk()
        ->assertSee('Programmes d\'Accompagnement');

    // Test du composant Livewire
    Livewire::test(ProgrammesCatalog::class)
        ->assertSee('AgriTech Booster')
        ->assertSee('FinTech Scale')
        ->set('search', 'AgriTech')
        ->assertSee('AgriTech Booster')
        ->assertDontSee('FinTech Scale')
        ->set('search', '')
        ->set('selectedStatus', 'open')
        ->assertSee('AgriTech Booster')
        ->call('resetFilters');

    // Test de l'action #[Json]
    $component = Livewire::test(ProgrammesCatalog::class);
    $stats = $component->instance()->getProgramQuickStats($programA->id);
    expect($stats['name'])->toBe('AgriTech Booster');
    expect($stats['is_open'])->toBeTrue();
});

test('public programme detail page renders with eligibility criteria and candidacy CTA', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Sahel Excellence',
        'slug' => 'incubateur-sahel-excellence',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Incubation Climat 2026',
        'status' => 'ACTIF',
        'description' => 'Programme intensif pour la transition écologique.',
        'eligibility' => 'Startups opérant dans les énergies renouvelables et gestion de l\'eau.',
        'registration_end' => now()->addDays(20),
    ]);

    $response = $this->get("/programmes/{$program->id}");

    $response->assertOk();
    $response->assertSee('Incubation Climat 2026');
    $response->assertSee('Incubateur Sahel Excellence');
    $response->assertSee('Éligibilité');
    $response->assertSee('Déposer ma Candidature');
    $response->assertSee("/programmes/{$program->id}/candidater");
});

test('new entrepreneur can submit complete application through livewire application wizard', function () {
    $saeOwner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $saeOwner->id,
        'name' => 'Hub Innov Cotonou',
        'slug' => 'hub-innov-cotonou',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Digital Inclusion West Africa',
        'status' => 'ACTIF',
        'description' => 'Programme panafricain pour l\'inclusion numérique.',
        'registration_end' => now()->addMonth(),
    ]);

    // Visite de la page de candidature
    $this->get("/programmes/{$program->id}/candidater")
        ->assertOk()
        ->assertSee('Digital Inclusion West Africa');

    // Soumission du wizard Livewire par un nouvel entrepreneur non connecté
    Livewire::test(ProgramApplicationWizard::class, ['program' => $program])
        ->set('first_name', 'Mamadou')
        ->set('last_name', 'Traoré')
        ->set('email', 'mamadou.traore@ecofuel.africa')
        ->set('phone', '+229 97 00 11 22')
        ->set('gender', 'M')
        ->set('city', 'Cotonou')
        ->set('password', 'SecretPassword123!')
        ->set('password_confirmation', 'SecretPassword123!')
        ->call('goToStep', 2)
        ->assertSet('currentStep', 2)
        ->set('project_name', 'EcoFuel Africa')
        ->set('sector', 'CleanTech / Énergie & Climat')
        ->set('pitch', 'Transformation des déchets agricoles en bio-combustible propre pour les ménages et PME.')
        ->set('team_size', 4)
        ->call('goToStep', 3)
        ->assertSet('currentStep', 3)
        ->set('motivation', 'Nous souhaitons bénéficier du cadre de mesure CUBE S&E et structurer notre levée de fonds d\'amorçage.')
        ->set('needs', ['Mentorat & Structuration', 'Levée de fonds & Financement'])
        ->call('submitApplication')
        ->assertHasNoErrors()
        ->assertSet('isSubmitted', true);

    // Vérifications en Base de Données
    $createdUser = User::where('email', 'mamadou.traore@ecofuel.africa')->first();
    expect($createdUser)->not->toBeNull();
    expect($createdUser->name)->toBe('Mamadou Traoré');

    $createdEntrepreneur = Entrepreneur::where('user_id', $createdUser->id)->first();
    expect($createdEntrepreneur)->not->toBeNull();
    expect($createdEntrepreneur->sae_id)->toBe($sae->id);

    $createdProject = Project::where('entrepreneur_id', $createdEntrepreneur->id)->first();
    expect($createdProject)->not->toBeNull();
    expect($createdProject->name)->toBe('EcoFuel Africa');
    expect($createdProject->status)->toBe(ProjectStatus::ACTIF);

    $application = Application::where('program_id', $program->id)
        ->where('entrepreneur_id', $createdEntrepreneur->id)
        ->first();
    expect($application)->not->toBeNull();
    expect($application->status)->toBe(ApplicationStatus::SOUMISE);
    expect($application->project->name)->toBe('EcoFuel Africa');
    expect($application->comment)->toContain('Nous souhaitons bénéficier du cadre de mesure CUBE S&E');

    // Vérifier l'authentification automatique
    expect(auth()->check())->toBeTrue();
    expect(auth()->id())->toBe($createdUser->id);
});

test('structures directory and orientation pages render properly', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Technopôle Bamako',
        'slug' => 'technopole-bamako',
        'address' => 'Bamako, Mali',
    ]);

    // 1. Annuaire des structures
    $respStruct = $this->get('/structures');
    $respStruct->assertOk();
    $respStruct->assertSee('Technopôle Bamako');

    // 2. Détail structure
    $respDetail = $this->get("/structures/{$sae->id}");
    $respDetail->assertOk();
    $respDetail->assertSee('Technopôle Bamako');

    // 3. Orientation / Rejoindre
    $respRejoindre = $this->get('/rejoindre');
    $respRejoindre->assertOk();
    $respRejoindre->assertSee('Je suis Entrepreneur');
    $respRejoindre->assertSee('Je suis Coach / Mentor');
    $respRejoindre->assertSee('Je représente une SAE');

    // 4. Redirection de rétrocompatibilité v1
    $respRedirect = $this->get('/account-type-selection');
    $respRedirect->assertRedirect('/rejoindre');
});
