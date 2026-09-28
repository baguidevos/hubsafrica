<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Level;
use App\Models\Program;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use App\Models\ProjectUpdateLog;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Alpha Incubator',
        'slug' => 'sae-alpha-incubator',
    ]);

    $this->level = Level::firstOrCreate(
        ['name' => 'Incubation Initiale'],
        ['sae_id' => $this->sae->id, 'order' => 1]
    );

    $this->program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme Croissance 2026',
        'status' => ProgramStatus::ACTIF,
        'level_id' => $this->level->id,
        'capacity' => 25,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);

    $this->cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'name' => 'Cohorte Tech 2026',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 15,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);

    $this->user = User::factory()->create([
        'name' => 'Aïda Diallo',
        'email' => 'aida.diallo@agro.sn',
    ]);

    $this->entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $this->user->id,
        'first_name' => 'Aïda',
        'last_name' => 'Diallo',
        'email' => 'aida.diallo@agro.sn',
        'gender' => 'F',
        'city' => 'Thiès',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'name' => 'AgroTech Sahel',
        'sector' => 'Agriculture',
        'territory' => 'Sénégal',
        'status' => ProjectStatus::ACTIF,
    ]);

    $this->application = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'cohort_id' => $this->cohort->id,
        'project_id' => $this->project->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);
});

test('une note superieure ou egale a 75 passe la candidature en ACCEPTEE et enrole le projet', function () {
    $this->application->applyEvaluationDecision(
        score: 82,
        status: null,
        juryComment: 'Excellent projet et fort potentiel de croissance'
    );

    $this->application->refresh();
    $this->project->refresh();

    // 1. Statut & score de la candidature
    expect($this->application->score)->toEqual(82)
        ->and($this->application->status)->toEqual(ApplicationStatus::ACCEPTEE)
        ->and($this->application->decision)->toContain('82/100')
        ->and($this->application->reviewed_at)->not->toBeNull();

    // 2. Enrôlement automatique du projet dans la cohorte
    $enrollment = Enrollment::where('cohort_id', $this->cohort->id)
        ->where('project_id', $this->project->id)
        ->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->status)->toEqual('ACTIF')
        ->and($enrollment->entrepreneur_id)->toEqual($this->entrepreneur->id);

    // 3. Mise à jour du projet (statut & niveau)
    expect($this->project->status)->toEqual(ProjectStatus::ACTIF)
        ->and($this->project->current_level_id)->toEqual($this->level->id);

    // 4. Historique de jalon & TimelineEvent
    $levelHistory = ProjectLevelHistory::where('project_id', $this->project->id)
        ->where('to_level_id', $this->level->id)
        ->first();
    expect($levelHistory)->not->toBeNull();

    $timeline = TimelineEvent::where('project_id', $this->project->id)
        ->where('type', 'MILESTONE')
        ->latest('id')
        ->first();
    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('82/100')
        ->and($timeline->description)->toContain('Cohorte Tech 2026');

    // 5. ProjectUpdateLog
    $log = ProjectUpdateLog::where('project_id', $this->project->id)->latest('id')->first();
    expect($log)->not->toBeNull()
        ->and($log->changes)->toBeArray();
});

test('une note entre 50 et 74 bascule la candidature en EN_ATTENTE sans enroler', function () {
    $this->application->applyEvaluationDecision(
        score: 62,
        status: null,
        juryComment: 'Dossier intéressant mais nécessite une consolidation du business model'
    );

    $this->application->refresh();

    expect($this->application->score)->toEqual(62)
        ->and($this->application->status)->toEqual(ApplicationStatus::EN_ATTENTE)
        ->and($this->application->decision)->toContain("liste d'attente");

    // Aucun enrôlement créé
    $enrollment = Enrollment::where('cohort_id', $this->cohort->id)
        ->where('project_id', $this->project->id)
        ->first();
    expect($enrollment)->toBeNull();

    // Timeline event enregistré
    $timeline = TimelineEvent::where('project_id', $this->project->id)
        ->where('type', 'NOTE')
        ->first();
    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain("Liste d'Attente");
});

test('une note inferieure a 50 bascule la candidature en REFUSEE', function () {
    $this->application->applyEvaluationDecision(
        score: 38,
        status: null,
        juryComment: 'Maturité insuffisante pour cette cohorte'
    );

    $this->application->refresh();

    expect($this->application->score)->toEqual(38)
        ->and($this->application->status)->toEqual(ApplicationStatus::REFUSEE)
        ->and($this->application->decision)->toContain('Dossier non retenu');

    // Timeline event REFUSEE
    $timeline = TimelineEvent::where('project_id', $this->project->id)
        ->where('type', 'NOTE')
        ->first();
    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('Non Retenu');
});

test('le jury peut forcer un statut indifferent de la note numerique', function () {
    // Note de 65 (normalement liste d'attente), mais forcé en ACCEPTEE
    $this->application->applyEvaluationDecision(
        score: 65,
        status: ApplicationStatus::ACCEPTEE,
        juryComment: 'Coup de coeur du jury'
    );

    $this->application->refresh();
    expect($this->application->score)->toEqual(65)
        ->and($this->application->status)->toEqual(ApplicationStatus::ACCEPTEE);

    $enrollment = Enrollment::where('cohort_id', $this->cohort->id)
        ->where('project_id', $this->project->id)
        ->first();
    expect($enrollment)->not->toBeNull();
});

test('les relations latestEnrollment et latestApplication fonctionnent sur Project', function () {
    $this->application->applyEvaluationDecision(score: 85);

    $this->project->refresh();

    expect($this->project->latestEnrollment)->not->toBeNull()
        ->and($this->project->latestEnrollment->cohort->name)->toEqual('Cohorte Tech 2026')
        ->and($this->project->latestApplication)->not->toBeNull()
        ->and($this->project->latestApplication->score)->toEqual(85);
});
