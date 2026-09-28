<?php

use App\Enums\ApplicationStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\FundingStatus;
use App\Enums\FundingType;
use App\Enums\Gender;
use App\Enums\IndicatorCategory;
use App\Enums\IndicatorStatus;
use App\Enums\ProjectStatus;
use App\Filament\Entrepreneur\Resources\Applications\Pages\ListApplications;
use App\Filament\Entrepreneur\Widgets\EntrepreneurStatsOverviewWidget;
use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Filament\Sae\Widgets\PerformanceRatesWidget;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Indicator;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('sae dashboard and enhanced pages load properly for sae user', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Test Hub',
        'slug' => 'sae-test-hub',
    ]);

    // Lier l'utilisateur comme tenant
    $user->saes()->attach($sae->id, ['role' => 'RESPONSABLE']);

    // Créer des entités de test
    $coach = Coach::create([
        'sae_id' => $sae->id,
        'name' => 'Coach Amadou',
        'email' => 'amadou@test.sn',
        'status' => 'ACTIF',
        'capacity' => 5,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Aissatou',
        'last_name' => 'Diallo',
        'email' => 'aissatou@test.sn',
        'gender' => 'F',
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'AgriBio Sahel',
        'sector' => 'Agriculture',
        'status' => 'ACTIF',
    ]);

    $session = CoachingSession::create([
        'sae_id' => $sae->id,
        'coach_id' => $coach->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'date' => now()->addDay(),
        'duration' => 60,
        'type' => 'INDIVIDUEL',
        'theme' => 'Revue du business plan',
        'status' => 'PLANIFIEE',
    ]);

    $this->actingAs($user);

    // 1. SAE Dashboard
    $respDash = $this->get("/sae/{$sae->slug}");
    $respDash->assertOk();

    // 2. View Project (Portefeuille cluster)
    $respProj = $this->get("/sae/{$sae->slug}/portefeuille/projects/{$project->id}");
    $respProj->assertOk();
    $respProj->assertSee('AgriBio Sahel');

    // 3. View Entrepreneur (Portefeuille cluster)
    $respEnt = $this->get("/sae/{$sae->slug}/portefeuille/entrepreneurs/{$entrepreneur->id}");
    $respEnt->assertOk();
    $respEnt->assertSee('Aissatou Diallo');

    // 4. View Coaching Session (Accompagnement cluster)
    $respSess = $this->get("/sae/{$sae->slug}/accompagnement/coaching-sessions/{$session->id}");
    $respSess->assertOk();
    $respSess->assertSee('Revue du business plan');

    // 5. Coaching Calendar Page (Accompagnement cluster)
    $respCal = $this->get("/sae/{$sae->slug}/accompagnement/coaching-calendar-page");
    $respCal->assertOk();
    $respCal->assertSee('Planning &amp; Calendrier du coaching', false);

    // 6. Analytics Page
    $respAnalytics = $this->get("/sae/{$sae->slug}/analytics-page");
    $respAnalytics->assertOk();
    $respAnalytics->assertSee('Entonnoir de sélection');

    // 7. Reports Page
    $respReports = $this->get("/sae/{$sae->slug}/reports-page");
    $respReports->assertOk();
    $respReports->assertSee('Cadre de mesure des résultats');

    // 8. Guide Page
    $respGuide = $this->get("/sae/{$sae->slug}/guide-page");
    $respGuide->assertOk();
    $respGuide->assertSee('BioAgri Tech');

    // 9. Test list pages of upgraded resources in clusters
    $this->get("/sae/{$sae->slug}/impact/funding-records")->assertOk();
    $this->get("/sae/{$sae->slug}/accompagnement/deliverable-submissions")->assertOk();
    $this->get("/sae/{$sae->slug}/accompagnement/deliverable-definitions")->assertOk();
    $this->get("/sae/{$sae->slug}/programmes/cohorts")->assertOk();
    $this->get("/sae/{$sae->slug}/programmes/cohort-phases")->assertOk();
    $this->get("/sae/{$sae->slug}/programmes/programs")->assertOk();
    $this->get("/sae/{$sae->slug}/accompagnement/coaches")->assertOk();
    $this->get("/sae/{$sae->slug}/accompagnement/coach-invitations")->assertOk();
    $this->get("/sae/{$sae->slug}/impact/indicators")->assertOk();
    $this->get("/sae/{$sae->slug}/programmes/levels")->assertOk();
    $this->get("/sae/{$sae->slug}/impact/data-room-documents")->assertOk();
    $this->get("/sae/{$sae->slug}/accompagnement/resources")->assertOk();
    $this->get("/sae/{$sae->slug}/portefeuille/timeline-events")->assertOk();
});

test('models cast enums properly with HasLabel and HasColor', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Enum Hub',
        'slug' => 'sae-enum-hub',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Khadija',
        'last_name' => 'Ndiaye',
        'email' => 'khadija@test.sn',
        'gender' => Gender::F,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    expect($entrepreneur->gender)->toBe(Gender::F);
    expect($entrepreneur->gender->getLabel())->toBe('Féminin');
    expect($entrepreneur->status)->toBe(EntrepreneurStatus::ACTIVE);
    expect($entrepreneur->status->getLabel())->toBe('Actif');

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Solaire Sahel',
        'sector' => 'Énergie',
        'status' => ProjectStatus::ACTIF,
    ]);

    expect($project->status)->toBe(ProjectStatus::ACTIF);
    expect($project->status->getLabel())->toBe('Actif');
    expect($project->status->getColor())->toBe('success');

    $funding = FundingRecord::create([
        'sae_id' => $sae->id,
        'project_id' => $project->id,
        'source' => 'DER/FJ',
        'type' => FundingType::SUBVENTION,
        'status' => FundingStatus::ACCORDE,
        'amount_obtained' => 5000000,
        'verified' => true,
    ]);

    expect($funding->status)->toBe(FundingStatus::ACCORDE);
    expect($funding->type)->toBe(FundingType::SUBVENTION);

    $def = DeliverableDefinition::create([
        'sae_id' => $sae->id,
        'name' => 'Étude de marché validée',
        'is_required' => true,
        'max_score' => 100,
        'passing_score' => 70,
        'max_attempts' => 3,
    ]);

    $submission = DeliverableSubmission::create([
        'sae_id' => $sae->id,
        'definition_id' => $def->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'version' => 1,
        'status' => DeliverableSubmissionStatus::SOUMIS,
        'submitted_at' => now(),
    ]);

    expect($submission->status)->toBe(DeliverableSubmissionStatus::SOUMIS);
    expect($submission->status->getLabel())->toBe('Déposé / Soumis');

    $indicator = Indicator::create([
        'sae_id' => $sae->id,
        'name' => 'Taux d\'insertion des jeunes',
        'category' => IndicatorCategory::PORTEE,
        'status' => IndicatorStatus::EN_BONNE_VOIE,
        'unit' => '%',
        'target' => 80,
        'current' => 85,
    ]);

    expect($indicator->category)->toBe(IndicatorCategory::PORTEE);
    expect($indicator->status)->toBe(IndicatorStatus::EN_BONNE_VOIE);
});

test('entrepreneur stats overview widget renders properly with ProjectStatus enum', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Test Hub Entrepreneur',
        'slug' => 'sae-test-hub-ent',
    ]);

    $entrepreneur = Entrepreneur::create([
        'user_id' => $user->id,
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Sow',
        'email' => $user->email,
        'gender' => 'F',
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'TechSahel Hub',
        'sector' => 'Technologies',
        'status' => ProjectStatus::ACTIF,
    ]);

    $this->actingAs($user);

    Livewire::test(EntrepreneurStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('TechSahel Hub')
        ->assertSee('Statut : Actif');
});

test('sae performance rates widget computes formulas and renders without SQL errors', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE Performance Rates Hub',
        'slug' => 'sae-perf-rates',
    ]);
    $user->saes()->attach($sae->id, ['role' => 'RESPONSABLE']);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Incubation Vert 2026',
        'slug' => 'incubation-vert-2026',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Alpha',
        'capacity' => 25,
        'status' => 'OUVERTE',
    ]);

    $this->actingAs($user);

    Livewire::test(PerformanceRatesWidget::class)
        ->assertSuccessful()
        ->assertSee('Taux de remplissage')
        ->assertSee('Charge par coach')
        ->assertSee('Taux de validation');
});

test('entrepreneur applications table does not display evaluate action and only coach/sae can evaluate', function () {
    $saeOwner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $saeOwner->id,
        'name' => 'SAE Eval Check',
        'slug' => 'sae-eval-check',
    ]);
    $saeOwner->saes()->attach($sae->id, ['role' => 'RESPONSABLE']);

    $userEntrepreneur = User::factory()->create();
    $entrepreneur = Entrepreneur::create([
        'user_id' => $userEntrepreneur->id,
        'sae_id' => $sae->id,
        'first_name' => 'Khadija',
        'last_name' => 'Sy',
        'email' => $userEntrepreneur->email,
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Program Test Eval Action',
        'status' => 'ACTIF',
    ]);

    $application = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    $userEntrepreneur->saes()->attach($sae->id, ['role' => 'ENTREPRENEUR']);

    // 1. En tant qu'entrepreneur dans le panel Entrepreneur
    $this->actingAs($userEntrepreneur);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($sae);

    Livewire::test(ListApplications::class)
        ->assertSuccessful()
        ->assertTableActionDoesNotExist('evaluate');

    // 2. En tant que responsable dans le panel SAE
    $this->actingAs($saeOwner);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    Livewire::test(App\Filament\Sae\Resources\Applications\Pages\ListApplications::class)
        ->assertSuccessful()
        ->assertTableActionExists('evaluate');
});

test('sae application resource and cluster display navigation badge for unprocessed applications', function () {
    $saeOwner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $saeOwner->id,
        'name' => 'SAE Badge Test Hub',
        'slug' => 'sae-badge-test-hub',
    ]);
    $saeOwner->saes()->attach($sae->id, ['role' => 'RESPONSABLE']);

    $userEntrepreneur = User::factory()->create();
    $entrepreneur = Entrepreneur::create([
        'user_id' => $userEntrepreneur->id,
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Bintou',
        'email' => $userEntrepreneur->email,
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Program Badge Test',
        'status' => 'ACTIF',
    ]);

    $this->actingAs($saeOwner);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    // Initialement 0 candidatures non traitées
    expect(ApplicationResource::getNavigationBadge())->toBeNull()
        ->and(ProgrammesCluster::getNavigationBadge())->toBeNull();

    // Création d'une nouvelle candidature non traitée (SOUMISE)
    $app1 = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    expect(ApplicationResource::getNavigationBadge())->toBe('1')
        ->and(ApplicationResource::getNavigationBadgeColor())->toBe('warning')
        ->and(ProgrammesCluster::getNavigationBadge())->toBe('1')
        ->and(ProgrammesCluster::getNavigationBadgeColor())->toBe('warning');

    // Dès que la candidature passe à un autre statut (ex: ACCEPTEE), le badge disparait
    $app1->update(['status' => ApplicationStatus::ACCEPTEE]);

    expect(ApplicationResource::getNavigationBadge())->toBeNull()
        ->and(ProgrammesCluster::getNavigationBadge())->toBeNull();
});
