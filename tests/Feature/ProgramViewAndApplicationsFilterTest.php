<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\Cohorts\Pages\ViewCohort;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortApplicationsRelationManager;
use App\Filament\Sae\Resources\Programs\Pages\ViewProgram;
use App\Filament\Sae\Resources\Programs\RelationManagers\ApplicationsRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\CohortsRelationManager;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Level;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\ProgramEvaluationPresenter;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Impact Hub Cotonou',
        'slug' => 'sae-impact-hub-cotonou',
    ]);

    $this->level = Level::firstOrCreate(
        ['name' => 'Accélération'],
        ['sae_id' => $this->sae->id, 'order' => 2]
    );

    $this->program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme GreenTech 2026',
        'description' => 'Accompagnement intensif pour les startups climatiques.',
        'objectives' => 'Structurer le produit et lever des fonds.',
        'eligibility' => 'Startups basées en Afrique de l\'Ouest.',
        'target' => 'GreenTech & AgriTech',
        'capacity' => 30,
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
        'level_id' => $this->level->id,
        'admission_threshold' => 75,
        'waitlist_threshold' => 50,
    ]);

    $this->firstCohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'name' => 'Cohorte 1 - Printemps',
        'number' => 1,
        'status' => CohortStatus::OUVERTE,
        'capacity' => 15,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);

    $this->secondCohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'name' => 'Cohorte 2 - Automne',
        'number' => 2,
        'status' => CohortStatus::CLOTUREE,
        'capacity' => 15,
        'start_date' => now()->subMonths(6),
        'end_date' => now()->subMonth(),
    ]);

    $firstCandidateUser = User::factory()->create();
    $this->firstEntrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $firstCandidateUser->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => 'koffi.mensah@ecobenin.bj',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->firstProject = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->firstEntrepreneur->id,
        'name' => 'EcoSolar Bénin',
        'sector' => 'CleanTech',
        'status' => ProjectStatus::ACTIF,
    ]);

    $this->firstApplication = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'cohort_id' => $this->firstCohort->id,
        'entrepreneur_id' => $this->firstEntrepreneur->id,
        'project_id' => $this->firstProject->id,
        'status' => ApplicationStatus::SOUMISE,
        'score' => null,
        'submitted_at' => now()->subHours(2),
    ]);

    $secondCandidateUser = User::factory()->create();
    $this->secondEntrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $secondCandidateUser->id,
        'first_name' => 'Amina',
        'last_name' => 'Bello',
        'email' => 'amina.bello@agritech.bj',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->secondProject = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->secondEntrepreneur->id,
        'name' => 'AgriSmart Parakou',
        'sector' => 'AgriTech',
        'status' => ProjectStatus::ACTIF,
    ]);

    $this->secondApplication = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'cohort_id' => $this->secondCohort->id,
        'entrepreneur_id' => $this->secondEntrepreneur->id,
        'project_id' => $this->secondProject->id,
        'status' => ApplicationStatus::ACCEPTEE,
        'score' => 84,
        'submitted_at' => now()->subDay(),
    ]);

    $adminUser = User::factory()->create();
    $this->actingAs($adminUser);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);
});

test('pure decision functions in ProgramEvaluationPresenter compute score badges and occupancy rates accurately', function () {
    expect(ProgramEvaluationPresenter::determineScoreBadgeColor(null))->toBe('gray')
        ->and(ProgramEvaluationPresenter::determineScoreBadgeColor(80, 75, 50))->toBe('success')
        ->and(ProgramEvaluationPresenter::determineScoreBadgeColor(60, 75, 50))->toBe('warning')
        ->and(ProgramEvaluationPresenter::determineScoreBadgeColor(40, 75, 50))->toBe('danger')
        ->and(ProgramEvaluationPresenter::formatScoreLabel(null))->toBe('Non notée')
        ->and(ProgramEvaluationPresenter::formatScoreLabel(84))->toBe('84 / 100')
        ->and(ProgramEvaluationPresenter::formatOccupancyRate(6, 20))->toBe('6 / 20 (30%)')
        ->and(ProgramEvaluationPresenter::formatOccupancyRate(4, null))->toBe('4 startup(s)')
        ->and(ProgramEvaluationPresenter::determineOccupancyColor(0, 15))->toBe('gray')
        ->and(ProgramEvaluationPresenter::determineOccupancyColor(5, 15))->toBe('primary')
        ->and(ProgramEvaluationPresenter::determineOccupancyColor(15, 15))->toBe('success');
});

test('view program page renders program details and metrics cleanly', function () {
    Livewire::test(ViewProgram::class, ['record' => $this->program->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('Programme GreenTech 2026')
        ->assertSee('Accompagnement intensif pour les startups climatiques.')
        ->assertSee('Structurer le produit et lever des fonds.')
        ->assertSee('GreenTech & AgriTech')
        ->assertSee('2 cohorte(s)')
        ->assertSee('2 dossier(s)')
        ->assertSee('1 retenue(s)')
        ->assertSee('1 en attente');
});

test('cohorts relation manager on view program lists cohorts and filters by status', function () {
    Livewire::test(CohortsRelationManager::class, [
        'ownerRecord' => $this->program,
        'pageClass' => ViewProgram::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->firstCohort, $this->secondCohort])
        ->filterTable('status', CohortStatus::OUVERTE->value)
        ->assertCanSeeTableRecords([$this->firstCohort])
        ->assertCanNotSeeTableRecords([$this->secondCohort]);
});

test('applications relation manager on view program lists applications and filters by cohort, status and sector', function () {
    Livewire::test(ApplicationsRelationManager::class, [
        'ownerRecord' => $this->program,
        'pageClass' => ViewProgram::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->firstApplication, $this->secondApplication])
        ->filterTable('cohort_id', $this->firstCohort->id)
        ->assertCanSeeTableRecords([$this->firstApplication])
        ->assertCanNotSeeTableRecords([$this->secondApplication])
        ->resetTableFilters()
        ->filterTable('status', ApplicationStatus::ACCEPTEE->value)
        ->assertCanSeeTableRecords([$this->secondApplication])
        ->assertCanNotSeeTableRecords([$this->firstApplication])
        ->resetTableFilters()
        ->filterTable('sector', 'CleanTech')
        ->assertCanSeeTableRecords([$this->firstApplication])
        ->assertCanNotSeeTableRecords([$this->secondApplication]);
});

test('view cohort page and cohort applications relation manager display cohort details and filter applications', function () {
    Livewire::test(ViewCohort::class, ['record' => $this->firstCohort->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('Cohorte 1 - Printemps')
        ->assertSee('Programme GreenTech 2026');

    Livewire::test(CohortApplicationsRelationManager::class, [
        'ownerRecord' => $this->firstCohort,
        'pageClass' => ViewCohort::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$this->firstApplication])
        ->assertCanNotSeeTableRecords([$this->secondApplication])
        ->filterTable('status', ApplicationStatus::SOUMISE->value)
        ->assertCanSeeTableRecords([$this->firstApplication]);
});
