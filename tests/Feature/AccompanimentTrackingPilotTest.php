<?php

use App\Enums\CoachingSessionStatus;
use App\Enums\CohortStatus;
use App\Enums\DeliverableResponsibleRole;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\DeliverableTarget;
use App\Enums\SessionPeriodOfDay;
use App\Filament\Sae\Pages\AccompanimentMonitoringDashboard;
use App\Filament\Sae\Pages\AccompanimentTemplatesPage;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\CohortPhase;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Program;
use App\Models\ProgramPhase;
use App\Models\Sae;
use App\Models\User;
use App\Services\AccompanimentTemplateService;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);

    $this->user = User::first() ?? User::factory()->create();
    $this->actingAs($this->user);

    Filament::setTenant($this->sae);
});

test('deliverable definition supports target_type, responsible_role, and sequence_order', function () {
    $definition = DeliverableDefinition::create([
        'sae_id' => $this->sae->id,
        'name' => 'Feuilles d\'émargement Formation 1',
        'target_type' => DeliverableTarget::PROGRAMME,
        'responsible_role' => DeliverableResponsibleRole::FORMATEUR,
        'sequence_order' => 4,
        'is_required' => true,
        'passing_score' => 70,
    ]);

    expect($definition->target_type)->toBe(DeliverableTarget::PROGRAMME)
        ->and($definition->responsible_role)->toBe(DeliverableResponsibleRole::FORMATEUR)
        ->and($definition->sequence_order)->toBe(4);
});

test('deliverable submission tracks cohort_id, allows null project for programme deliverables, and detects late submissions', function () {
    $program = Program::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Startup Loop Pilote'],
        ['status' => 'ACTIF']
    );

    $cohort = Cohort::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Promotion Pilote 2026'],
        ['program_id' => $program->id, 'status' => CohortStatus::EN_COURS]
    );

    $definition = DeliverableDefinition::create([
        'sae_id' => $this->sae->id,
        'name' => 'Rapport mi-parcours M&E',
        'target_type' => DeliverableTarget::PROGRAMME,
        'responsible_role' => DeliverableResponsibleRole::CHARGE_ME,
        'deadline' => now()->subDay(),
        'sequence_order' => 8,
    ]);

    // Soumission après la deadline => is_late doit être automatiquement true
    $submission = DeliverableSubmission::create([
        'sae_id' => $this->sae->id,
        'definition_id' => $definition->id,
        'cohort_id' => $cohort->id,
        'project_id' => null,
        'status' => DeliverableSubmissionStatus::SOUMIS,
        'submitted_at' => now(),
    ]);

    expect($submission->cohort_id)->toBe($cohort->id)
        ->and($submission->project_id)->toBeNull()
        ->and($submission->is_late)->toBeTrue();
});

test('coaching session tracks session_number, period_of_day, and hours_delivered', function () {
    $coach = Coach::firstOrCreate(
        ['sae_id' => $this->sae->id, 'name' => 'Expert Coach Test'],
        ['email' => 'coach.test@sae.org', 'status' => 'ACTIF']
    );

    $session = CoachingSession::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'date' => now(),
        'duration' => 90,
        'session_number' => 3,
        'period_of_day' => SessionPeriodOfDay::MATIN,
        'hours_delivered' => 1.5,
        'type' => 'INDIVIDUEL',
        'status' => CoachingSessionStatus::REALISEE,
    ]);

    expect($session->session_number)->toBe(3)
        ->and($session->period_of_day)->toBe(SessionPeriodOfDay::MATIN)
        ->and((float) $session->hours_delivered)->toBe(1.50);
});

test('accompaniment monitoring dashboard component loads and calculates KPIs correctly', function () {
    $cohort = Cohort::where('sae_id', $this->sae->id)->first();

    $component = Livewire::test(AccompanimentMonitoringDashboard::class);

    $component->assertSuccessful();

    $kpis = $component->get('kpis');
    expect($kpis)->toBeArray()
        ->and($kpis)->toHaveKeys([
            'startups_count',
            'avancement_programme',
            'livrables_programme_valides',
            'livrables_programme_total',
            'avancement_startups',
            'livrables_startups_valides',
            'taux_presence_formation',
            'taux_presence_coaching',
            'heures_coaching_total',
            'alertes_presence_count',
            'soumissions_en_retard',
        ]);
});

test('accompaniment monitoring dashboard can export CSV summary', function () {
    $component = Livewire::test(AccompanimentMonitoringDashboard::class);

    $response = $component->call('exportCsv');

    expect($response)->not->toBeNull();
});

test('accompaniment template service applies full pack to program and cohort', function () {
    $service = app(AccompanimentTemplateService::class);
    expect($service->all())->toHaveKey('startup-loop-africinnov');

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme Test Déploiement Template',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion Test Template',
        'status' => CohortStatus::EN_COURS,
    ]);

    $summary = $service->apply('startup-loop-africinnov', $this->sae, $program, $cohort);

    expect($summary['phases'])->toBe(6)
        ->and($summary['startup_deliverables'])->toBe(18)
        ->and($summary['programme_deliverables'])->toBe(14);

    // Vérification que les 6 phases sont créées pour ce programme
    expect(ProgramPhase::where('program_id', $program->id)->count())->toBe(6);

    // Vérification que les phases sont synchronisées sur la cohorte
    expect(CohortPhase::where('cohort_id', $cohort->id)->count())->toBe(6);
});

test('accompaniment templates page loads successfully and displays both templates', function () {
    Livewire::test(AccompanimentTemplatesPage::class)
        ->assertSuccessful()
        ->assertSee('Startup Loop (Afric\'innov / SAE)')
        ->assertSee('Méthodologie CUBE (SAEI CUBE & KPITAL INVEST)')
        ->assertSee('CUBE & KPITAL INVEST');
});

test('accompaniment templates deploy action mounts cleanly with arguments', function () {
    Livewire::test(AccompanimentTemplatesPage::class)
        ->mountAction('deploy', [
            'template_key' => 'startup-loop-africinnov',
            'template_title' => 'Startup Loop (Afric\'innov / SAE)',
        ])
        ->assertActionMounted('deploy')
        ->assertActionDataSet([
            'template_key' => 'startup-loop-africinnov',
        ]);
});

test('cube methodology template deploys full pack with PAS, OHADA, and KPITAL INVEST to program and cohort', function () {
    $service = app(AccompanimentTemplateService::class);
    expect($service->all())->toHaveKey('cube-saei-kpital');

    $cubeTemplate = $service->get('cube-saei-kpital');
    expect($cubeTemplate->getBadge())->toBe('CUBE & KPITAL INVEST')
        ->and($cubeTemplate->getDurationWeeks())->toBe(26)
        ->and(count($cubeTemplate->getPhases()))->toBe(6)
        ->and(count($cubeTemplate->getStartupDeliverables()))->toBe(24)
        ->and(count($cubeTemplate->getProgrammeDeliverables()))->toBe(15)
        ->and(count($cubeTemplate->getEvaluationCriteria()))->toBe(5)
        ->and(count($cubeTemplate->getMneIndicators()))->toBe(6);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme CUBE Incubateur 2026',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion CUBE Togo 1',
        'status' => CohortStatus::EN_COURS,
    ]);

    $summary = $service->apply('cube-saei-kpital', $this->sae, $program, $cohort);

    expect($summary['phases'])->toBe(6)
        ->and($summary['startup_deliverables'])->toBe(24)
        ->and($summary['programme_deliverables'])->toBe(15)
        ->and($summary['criteria'])->toBe(5)
        ->and($summary['indicators'])->toBe(6);

    expect(ProgramPhase::where('program_id', $program->id)->count())->toBe(6);
    expect(CohortPhase::where('cohort_id', $cohort->id)->count())->toBe(6);
});
