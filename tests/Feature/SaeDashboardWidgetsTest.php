<?php

use App\Enums\ApplicationStatus;
use App\Enums\CoachStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Widgets\ApplicationsChartWidget;
use App\Filament\Sae\Widgets\CoachWorkloadWidget;
use App\Filament\Sae\Widgets\PerformanceRatesWidget;
use App\Filament\Sae\Widgets\PortfolioSectorChartWidget;
use App\Filament\Sae\Widgets\SaeStatsOverviewWidget;
use App\Livewire\Public\ProgramApplicationWizard;
use App\Models\Application;
use App\Models\Coach;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('sae dashboard widgets do not fabricate fallback data when empty', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Vide',
        'slug' => 'sae-vide-'.uniqid(),
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);

    Livewire::test(SaeStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Entrepreneurs accompagnés')
        ->assertSee('Appels à candidatures')
        ->assertSee('Financements décaissés')
        ->assertSee('accordés en cours');

    $rates = app(PerformanceRatesWidget::class)->getFormulesData();
    $byKey = collect($rates)->keyBy('key');

    expect($byKey['validation']['val'])->toBe('0 %')
        ->and($byKey['remplissage']['val'])->toBe('0 %')
        ->and($byKey['remplissage']['den'])->toBe('Capacité non définie (0)')
        ->and($byKey['seances']['val'])->toBe('0 %')
        ->and($byKey['financement']['val'])->toBe('0 %')
        ->and($byKey['chargeCoach']['tone'])->toBe('rose');

    $sectorChart = new class extends PortfolioSectorChartWidget
    {
        public function exposedData(): array
        {
            return $this->getData();
        }
    };
    expect($sectorChart->exposedData()['labels'])->toBeEmpty();

    $appChart = new class extends ApplicationsChartWidget
    {
        public function exposedData(): array
        {
            return $this->getData();
        }
    };
    expect(array_sum($appChart->exposedData()['datasets'][0]['data']))->toBe(0);
});

test('sae dashboard widgets count both active status variants and exclude draft applications', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Active',
        'slug' => 'sae-active-'.uniqid(),
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);

    $programLance = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Lancé',
        'status' => ProgramStatus::LANCE,
    ]);

    Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Actif',
        'status' => ProgramStatus::ACTIF,
    ]);

    Coach::create([
        'sae_id' => $sae->id,
        'name' => 'Coach FR',
        'email' => 'coach-fr@example.com',
        'status' => CoachStatus::ACTIF,
    ]);

    Coach::create([
        'sae_id' => $sae->id,
        'name' => 'Coach EN',
        'email' => 'coach-en@example.com',
        'status' => CoachStatus::ACTIVE,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Awa',
        'last_name' => 'Diallo',
        'email' => 'awa@example.com',
        'status' => 'ACTIVE',
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programLance->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::BROUILLON,
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programLance->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);

    $stats = app(SaeStatsOverviewWidget::class);
    Livewire::test(SaeStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('2') // 2 programmes actifs, 2 coachs mobilisés
        ->assertSee('1'); // 1 candidature reçue (brouillon exclu)

    $workloads = app(CoachWorkloadWidget::class)->getCoachWorkloads();
    expect($workloads)->toHaveCount(2);

    $rates = collect(app(PerformanceRatesWidget::class)->getFormulesData())->keyBy('key');
    expect($rates['chargeCoach']['tone'])->toBe('emerald')
        ->and($rates['chargeCoach']['den'])->toBe('Coachs actifs (2)');
});

test('submitted candidate starts as CANDIDAT with portal access and is promoted to ACTIVE on acceptance', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Sélection',
        'slug' => 'sae-selection-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Incubation',
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 1',
        'status' => 'OUVERTE',
    ]);

    Livewire::test(ProgramApplicationWizard::class, ['program' => $program])
        ->set('first_name', 'Moussa')
        ->set('last_name', 'Ndiaye')
        ->set('email', 'moussa.candidat@example.com')
        ->set('password', 'password123')
        ->set('phone', '+221770000000')
        ->set('gender', 'M')
        ->set('city', 'Dakar')
        ->set('project_name', 'AgriTech Senegal')
        ->set('sector', 'Agriculture')
        ->set('pitch', 'Plateforme agricole')
        ->set('team_size', 2)
        ->set('motivation', 'Développer notre solution.')
        ->call('submitApplication')
        ->assertHasNoErrors();

    $candidateUser = User::where('email', 'moussa.candidat@example.com')->firstOrFail();
    $entrepreneur = Entrepreneur::where('user_id', $candidateUser->id)->firstOrFail();
    $project = Project::where('entrepreneur_id', $entrepreneur->id)->firstOrFail();
    $application = Application::where('project_id', $project->id)->firstOrFail();

    expect($entrepreneur->status)->toBe(EntrepreneurStatus::CANDIDAT)
        ->and($project->status)->toBe(ProjectStatus::CANDIDAT)
        ->and($application->status)->toBe(ApplicationStatus::SOUMISE)
        ->and($candidateUser->canAccessPanel(Filament::getPanel('entrepreneur')))->toBeTrue()
        ->and($candidateUser->getTenants(Filament::getPanel('entrepreneur'))->pluck('id'))->toContain($sae->id);

    // Le dashboard SAE ne doit pas compter le candidat dans les projets/entrepreneurs actifs
    $this->actingAs($owner);
    Filament::setTenant($sae);

    expect(Entrepreneur::where('status', EntrepreneurStatus::ACTIVE->value)->count())->toBe(0)
        ->and(Project::where('status', ProjectStatus::ACTIF->value)->count())->toBe(0);

    // Acceptation par le jury -> promotion automatique en ACTIVE / ACTIF
    $application->update(['cohort_id' => $cohort->id, 'status' => ApplicationStatus::ACCEPTEE]);

    expect($entrepreneur->fresh()->status)->toBe(EntrepreneurStatus::ACTIVE)
        ->and($project->fresh()->status)->toBe(ProjectStatus::ACTIF);
});

test('sae:fix-candidate-stats command backfills legacy candidate statuses safely without touching accepted or manual records', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Prod',
        'slug' => 'sae-prod-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Prod',
        'status' => ProgramStatus::ACTIF,
    ]);

    // 1. Ancien candidat soumis avant le patch (statuts restés ACTIVE / ACTIF et sans pivot sae_user)
    $legacyUser = User::factory()->create();
    $legacyEntrepreneur = Entrepreneur::create([
        'user_id' => $legacyUser->id,
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Sow',
        'email' => $legacyUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $legacyProject = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $legacyEntrepreneur->id,
        'name' => 'Projet Soumis',
        'status' => ProjectStatus::ACTIF,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $legacyEntrepreneur->id,
        'project_id' => $legacyProject->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);

    // 2. Projet créé manuellement (sans candidature) -> doit rester ACTIF / ACTIVE
    $manualEntrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Ibrahima',
        'last_name' => 'Ba',
        'email' => 'ibrahima@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $manualProject = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $manualEntrepreneur->id,
        'name' => 'Projet Manuel',
        'status' => ProjectStatus::ACTIF,
    ]);

    // Dry-run ne modifie rien
    $this->artisan('sae:fix-candidate-stats', ['--dry-run' => true])->assertSuccessful();
    expect($legacyProject->fresh()->status)->toBe(ProjectStatus::ACTIF)
        ->and($legacyEntrepreneur->fresh()->status)->toBe(EntrepreneurStatus::ACTIVE);

    // Exécution réelle
    $this->artisan('sae:fix-candidate-stats')->assertSuccessful();

    expect($legacyProject->fresh()->status)->toBe(ProjectStatus::CANDIDAT)
        ->and($legacyEntrepreneur->fresh()->status)->toBe(EntrepreneurStatus::CANDIDAT)
        ->and($legacyUser->saes()->where('saes.id', $sae->id)->exists())->toBeTrue()
        ->and($manualProject->fresh()->status)->toBe(ProjectStatus::ACTIF)
        ->and($manualEntrepreneur->fresh()->status)->toBe(EntrepreneurStatus::ACTIVE);
});

test('maintenance route for fix-candidate-stats requires authorization and runs with valid key', function () {
    $this->get('/maintenance/fix-candidate-stats')->assertForbidden();

    $secret = env('MIGRATION_SECRET', 'hubsafrica-migrate-force-2026');

    $this->get("/maintenance/fix-candidate-stats?key={$secret}&dry_run=1")
        ->assertOk()
        ->assertSee('SIMULATION SAE:FIX-CANDIDATE-STATS');
});

test('cohort public registration creates entrepreneur with CANDIDAT status and keeps portal access', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Cohorte',
        'slug' => 'sae-cohorte-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Cohorte',
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Publique',
        'status' => 'OUVERTE',
    ]);

    $this->post(route('public.cohort.register', ['id' => $cohort->id]), [
        'first_name' => 'Aissatou',
        'last_name' => 'Ba',
        'email' => 'aissatou.cohorte@example.com',
        'phone' => '+221770001122',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertRedirect();

    $user = User::where('email', 'aissatou.cohorte@example.com')->firstOrFail();
    $entrepreneur = Entrepreneur::where('user_id', $user->id)->firstOrFail();

    expect($entrepreneur->status)->toBe(EntrepreneurStatus::CANDIDAT)
        ->and($user->saes()->where('saes.id', $sae->id)->exists())->toBeTrue();
});
