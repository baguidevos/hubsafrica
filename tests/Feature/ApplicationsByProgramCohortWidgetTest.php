<?php

use App\Enums\ApplicationStatus;
use App\Enums\ProgramStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Filament\Sae\Widgets\ApplicationsByProgramCohortWidget;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('applications by program and cohort widget shows empty state without fabricating data', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Vide Repartition',
        'slug' => 'sae-vide-repartition-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Brouillon',
        'status' => ProgramStatus::ACTIF,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Ndiaye',
        'email' => 'fatou.draft@example.com',
        'status' => 'CANDIDAT',
    ]);

    // Les brouillons ne doivent pas être comptabilisés
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::BROUILLON,
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('sae'));

    $distribution = app(ApplicationsByProgramCohortWidget::class)->getDistributionData();

    expect($distribution['total_applications'])->toBe(0)
        ->and($distribution['programs'])->toBeEmpty();

    Livewire::test(ApplicationsByProgramCohortWidget::class)
        ->assertSuccessful()
        ->assertSee('Répartition des candidatures par programme et cohorte')
        ->assertSee('Aucune candidature reçue pour le moment.');
});

test('applications by program and cohort widget aggregates counts, respects tenant isolation, and filters by program', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Principale',
        'slug' => 'sae-principale-'.uniqid(),
    ]);

    $otherOwner = User::factory()->create();
    $otherSae = Sae::create([
        'owner_id' => $otherOwner->id,
        'name' => 'Autre SAE',
        'slug' => 'autre-sae-'.uniqid(),
    ]);

    $programA = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Incubation',
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
    ]);

    $programB = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Accélération',
        'status' => ProgramStatus::ACTIF,
    ]);

    $otherProgram = Program::create([
        'sae_id' => $otherSae->id,
        'name' => 'Programme Autre SAE',
        'status' => ProgramStatus::ACTIF,
    ]);

    $cohortA1 = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'name' => 'Cohorte 2026-1',
        'status' => 'OUVERTE',
    ]);

    $cohortA2 = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'name' => 'Cohorte 2026-2',
        'status' => 'OUVERTE',
    ]);

    $cohortB1 = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $programB->id,
        'name' => 'Promo Scale 1',
        'status' => 'EN_COURS',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Moussa',
        'last_name' => 'Diop',
        'email' => 'moussa.repartition@example.com',
        'status' => 'CANDIDAT',
    ]);

    $otherEntrepreneur = Entrepreneur::create([
        'sae_id' => $otherSae->id,
        'first_name' => 'Aminata',
        'last_name' => 'Fall',
        'email' => 'aminata.other@example.com',
        'status' => 'CANDIDAT',
    ]);

    // Programme A : 3 candidatures valides (2 sur A1, 1 sans cohorte) + 1 brouillon exclu
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'cohort_id' => $cohortA1->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::ACCEPTEE,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'cohort_id' => $cohortA1->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'cohort_id' => null,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::REFUSEE,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'cohort_id' => $cohortA2->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::BROUILLON,
    ]);

    // Programme B : 1 candidature en examen
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programB->id,
        'cohort_id' => $cohortB1->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::EN_EXAMEN,
    ]);

    // Autre SAE : ne doit jamais apparaître dans le widget du tenant actif
    Application::create([
        'sae_id' => $otherSae->id,
        'program_id' => $otherProgram->id,
        'entrepreneur_id' => $otherEntrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('sae'));

    $distribution = app(ApplicationsByProgramCohortWidget::class)->getDistributionData();

    expect($distribution['total_applications'])->toBe(4)
        ->and($distribution['programs_count'])->toBe(2)
        ->and($distribution['cohorts_count'])->toBe(2)
        ->and($distribution['programs'])->toHaveCount(2)
        ->and($distribution['programs'][0]['program_name'])->toBe('Programme Incubation')
        ->and($distribution['programs'][0]['total'])->toBe(3)
        ->and($distribution['programs'][0]['share'])->toBe(75.0)
        ->and($distribution['programs'][0]['accepted'])->toBe(1)
        ->and($distribution['programs'][0]['pending'])->toBe(1)
        ->and($distribution['programs'][0]['rejected'])->toBe(1)
        ->and($distribution['programs'][0]['cohorts'])->toHaveCount(2)
        ->and($distribution['programs'][0]['cohorts'][0]['cohort_name'])->toBe('Cohorte 2026-1')
        ->and($distribution['programs'][0]['cohorts'][0]['total'])->toBe(2)
        ->and($distribution['programs'][0]['cohorts'][1]['cohort_name'])->toBe('Sans cohorte assignée')
        ->and($distribution['programs'][0]['cohorts'][1]['total'])->toBe(1)
        ->and($distribution['programs'][1]['program_name'])->toBe('Programme Accélération')
        ->and($distribution['programs'][1]['total'])->toBe(1)
        ->and($distribution['programs'][1]['share'])->toBe(25.0);

    $cohortUrl = ApplicationResource::getUrl('index', [
        'tenant' => $sae,
        'filters' => [
            'program_id' => ['value' => $programA->id],
            'cohort_id' => ['value' => $cohortA1->id],
        ],
    ], panel: 'sae');

    Livewire::test(ApplicationsByProgramCohortWidget::class)
        ->assertSuccessful()
        ->assertSee('Programme Incubation')
        ->assertSee('Programme Accélération')
        ->assertSee('Cohorte 2026-1')
        ->assertSee('Sans cohorte assignée')
        ->assertSee('Promo Scale 1')
        ->assertDontSee('Programme Autre SAE')
        ->assertSee($cohortUrl)
        ->set('selectedProgramId', (string) $programB->id)
        ->assertSee('Promo Scale 1')
        ->assertDontSee('Cohorte 2026-1')
        ->assertDontSee('Sans cohorte assignée');
});

test('sae dashboard displays the applications by program and cohort widget', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Dashboard Check',
        'slug' => 'sae-dashboard-check-'.uniqid(),
    ]);

    $this->actingAs($owner);

    $this->get(route('filament.sae.pages.dashboard', ['tenant' => $sae]))
        ->assertOk()
        ->assertSeeLivewire(ApplicationsByProgramCohortWidget::class);
});
