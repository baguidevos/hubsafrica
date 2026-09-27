<?php

use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;

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

    // 2. View Project
    $respProj = $this->get("/sae/{$sae->slug}/projects/{$project->id}");
    $respProj->assertOk();
    $respProj->assertSee('AgriBio Sahel');

    // 3. View Entrepreneur
    $respEnt = $this->get("/sae/{$sae->slug}/entrepreneurs/{$entrepreneur->id}");
    $respEnt->assertOk();
    $respEnt->assertSee('Aissatou Diallo');

    // 4. View Coaching Session
    $respSess = $this->get("/sae/{$sae->slug}/coaching-sessions/{$session->id}");
    $respSess->assertOk();
    $respSess->assertSee('Revue du business plan');

    // 5. Coaching Calendar Page
    $respCal = $this->get("/sae/{$sae->slug}/coaching-calendar-page");
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

    // 8. Test list pages of upgraded resources
    $this->get("/sae/{$sae->slug}/funding-records")->assertOk();
    $this->get("/sae/{$sae->slug}/deliverable-submissions")->assertOk();
    $this->get("/sae/{$sae->slug}/deliverable-definitions")->assertOk();
    $this->get("/sae/{$sae->slug}/cohorts")->assertOk();
    $this->get("/sae/{$sae->slug}/programs")->assertOk();
    $this->get("/sae/{$sae->slug}/coaches")->assertOk();
    $this->get("/sae/{$sae->slug}/coach-invitations")->assertOk();
    $this->get("/sae/{$sae->slug}/indicators")->assertOk();
    $this->get("/sae/{$sae->slug}/levels")->assertOk();
    $this->get("/sae/{$sae->slug}/data-room-documents")->assertOk();
    $this->get("/sae/{$sae->slug}/resources")->assertOk();
    $this->get("/sae/{$sae->slug}/timeline-events")->assertOk();
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
        'gender' => \App\Enums\Gender::F,
        'status' => \App\Enums\EntrepreneurStatus::ACTIVE,
    ]);

    expect($entrepreneur->gender)->toBe(\App\Enums\Gender::F);
    expect($entrepreneur->gender->getLabel())->toBe('Féminin');
    expect($entrepreneur->status)->toBe(\App\Enums\EntrepreneurStatus::ACTIVE);
    expect($entrepreneur->status->getLabel())->toBe('Actif');

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Solaire Sahel',
        'sector' => 'Énergie',
        'status' => \App\Enums\ProjectStatus::ACTIF,
    ]);

    expect($project->status)->toBe(\App\Enums\ProjectStatus::ACTIF);
    expect($project->status->getLabel())->toBe('Actif');
    expect($project->status->getColor())->toBe('success');

    $funding = \App\Models\FundingRecord::create([
        'sae_id' => $sae->id,
        'project_id' => $project->id,
        'source' => 'DER/FJ',
        'type' => \App\Enums\FundingType::SUBVENTION,
        'status' => \App\Enums\FundingStatus::ACCORDE,
        'amount_obtained' => 5000000,
        'verified' => true,
    ]);

    expect($funding->status)->toBe(\App\Enums\FundingStatus::ACCORDE);
    expect($funding->type)->toBe(\App\Enums\FundingType::SUBVENTION);

    $def = \App\Models\DeliverableDefinition::create([
        'sae_id' => $sae->id,
        'name' => 'Étude de marché validée',
        'is_required' => true,
        'max_score' => 100,
        'passing_score' => 70,
        'max_attempts' => 3,
    ]);

    $submission = \App\Models\DeliverableSubmission::create([
        'sae_id' => $sae->id,
        'definition_id' => $def->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'version' => 1,
        'status' => \App\Enums\DeliverableSubmissionStatus::SOUMIS,
        'submitted_at' => now(),
    ]);

    expect($submission->status)->toBe(\App\Enums\DeliverableSubmissionStatus::SOUMIS);
    expect($submission->status->getLabel())->toBe('Déposé / Soumis');

    $indicator = \App\Models\Indicator::create([
        'sae_id' => $sae->id,
        'name' => 'Taux d\'insertion des jeunes',
        'category' => \App\Enums\IndicatorCategory::PORTEE,
        'status' => \App\Enums\IndicatorStatus::EN_BONNE_VOIE,
        'unit' => '%',
        'target' => 80,
        'current' => 85,
    ]);

    expect($indicator->category)->toBe(\App\Enums\IndicatorCategory::PORTEE);
    expect($indicator->status)->toBe(\App\Enums\IndicatorStatus::EN_BONNE_VOIE);
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
        'status' => \App\Enums\ProjectStatus::ACTIF,
    ]);

    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Entrepreneur\Widgets\EntrepreneurStatsOverviewWidget::class)
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

    $program = \App\Models\Program::create([
        'sae_id' => $sae->id,
        'name' => 'Incubation Vert 2026',
        'slug' => 'incubation-vert-2026',
        'status' => 'ACTIF',
    ]);

    $cohort = \App\Models\Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Alpha',
        'capacity' => 25,
        'status' => 'OUVERTE',
    ]);

    $this->actingAs($user);

    \Livewire\Livewire::test(\App\Filament\Sae\Widgets\PerformanceRatesWidget::class)
        ->assertSuccessful()
        ->assertSee('Taux de remplissage')
        ->assertSee('Charge par coach')
        ->assertSee('Taux de validation');
});



