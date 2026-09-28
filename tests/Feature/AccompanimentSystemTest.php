<?php

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\CoachingSessionType;
use App\Enums\DocumentCategory;
use App\Enums\PartnerType;
use App\Enums\ProgramPhaseStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\CoachingSessions\Pages\EditCoachingSession;
use App\Filament\Sae\Resources\CoachingSessions\RelationManagers\AttendeesRelationManager;
use App\Filament\Sae\Resources\Cohorts\Pages\EditCohort;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortSessionsRelationManager;
use App\Filament\Sae\Resources\Programs\Pages\EditProgram;
use App\Filament\Sae\Resources\Programs\RelationManagers\PartnersRelationManager;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\CoachingSessionAttendee;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\EvaluationCriterion;
use App\Models\Indicator;
use App\Models\IndicatorEntry;
use App\Models\Partner;
use App\Models\Program;
use App\Models\ProgramPhase;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\AttendanceService;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);
});

test('program phase templates can be defined and synced to cohort with calculated sequential dates', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Green Agri Accélération',
        'status' => 'ACTIF',
    ]);

    $phase1 = ProgramPhase::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Diagnostic & Cadrage',
        'position' => 1,
        'duration_weeks' => 3,
    ]);

    $phase2 = ProgramPhase::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Déploiement Commercial',
        'position' => 2,
        'duration_weeks' => 6,
    ]);

    $startDate = now()->addDays(5)->startOfDay();

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion Green 2026',
        'start_date' => $startDate,
    ]);

    $cohort->syncPhasesFromProgram();

    $phases = $cohort->phases()->orderBy('position')->get();

    expect($phases)->toHaveCount(2)
        ->and($phases[0]->name)->toBe('Diagnostic & Cadrage')
        ->and($phases[0]->starts_on->toDateString())->toBe($startDate->toDateString())
        ->and($phases[0]->status)->toBe(ProgramPhaseStatus::A_VENIR)
        ->and($phases[1]->name)->toBe('Déploiement Commercial')
        ->and($phases[1]->starts_on->toDateString())->toBe($phases[0]->ends_on->copy()->addDay()->toDateString());
});

test('jury criteria scoring calculates weighted score and updates parent application score', function () {
    $user = User::factory()->create();
    $evaluator = User::factory()->create();

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Cheikh',
        'last_name' => 'Ndiaye',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Digital Tech Africa',
        'status' => 'ACTIF',
    ]);

    $critProblem = EvaluationCriterion::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Proposition de valeur',
        'weight' => 2,
        'max_score' => 10,
        'position' => 1,
    ]);

    $critTeam = EvaluationCriterion::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Qualité de l\'équipe',
        'weight' => 3,
        'max_score' => 10,
        'position' => 2,
    ]);

    $application = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => 'SOUMISE',
    ]);

    $evaluation = ApplicationEvaluation::create([
        'sae_id' => $this->sae->id,
        'application_id' => $application->id,
        'evaluator_id' => $evaluator->id,
        'comment' => 'Dossier très prometteur',
        'status' => ApplicationEvaluationStatus::SOUMIS,
        'submitted_at' => now(),
    ]);

    // Problem: 8/10 (normalized = 80%) * weight 2 = 160
    ApplicationEvaluationScore::create([
        'evaluation_id' => $evaluation->id,
        'criterion_id' => $critProblem->id,
        'score' => 8.00,
    ]);

    // Team: 9/10 (normalized = 90%) * weight 3 = 270
    ApplicationEvaluationScore::create([
        'evaluation_id' => $evaluation->id,
        'criterion_id' => $critTeam->id,
        'score' => 9.00,
    ]);

    // Total = (160 + 270) / (2 + 3) = 430 / 5 = 86.00
    $score = $evaluation->calculateWeightedScore();

    expect($score)->toBe(86.0)
        ->and((float) $evaluation->fresh()->total_score)->toBe(86.0)
        ->and($application->fresh()->score)->toBe(86);
});

test('polymorphic documents can be uploaded with versioning and retrieved via latestDocument', function () {
    $user = User::factory()->create();

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Mariama',
        'last_name' => 'Sow',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'PayDakar',
        'status' => ProjectStatus::ACTIF,
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Fintech Incubator',
        'status' => 'ACTIF',
    ]);

    $definition = DeliverableDefinition::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Business Model Canvas',
    ]);

    $submission = DeliverableSubmission::create([
        'sae_id' => $this->sae->id,
        'definition_id' => $definition->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'version' => 1,
    ]);

    // Version 1
    $docV1 = Document::create([
        'sae_id' => $this->sae->id,
        'documentable_type' => DeliverableSubmission::class,
        'documentable_id' => $submission->id,
        'category' => DocumentCategory::LIVRABLE,
        'title' => 'BMC v1 Initial',
        'path' => 'deliverables/bmc_v1.pdf',
        'original_name' => 'bmc_v1.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1024000,
        'version' => 1,
        'uploaded_by_id' => $user->id,
    ]);

    // Version 2
    $docV2 = Document::create([
        'sae_id' => $this->sae->id,
        'documentable_type' => DeliverableSubmission::class,
        'documentable_id' => $submission->id,
        'category' => DocumentCategory::LIVRABLE,
        'title' => 'BMC v2 Final',
        'path' => 'deliverables/bmc_v2.pdf',
        'original_name' => 'bmc_v2.pdf',
        'mime_type' => 'application/pdf',
        'size' => 1500000,
        'version' => 2,
        'uploaded_by_id' => $user->id,
    ]);

    expect($submission->documents)->toHaveCount(2)
        ->and($submission->latestDocument->version)->toBe(2)
        ->and($submission->latestDocument->title)->toBe('BMC v2 Final')
        ->and($docV1->human_size)->toContain('Ko')
        ->and($docV2->human_size)->toContain('Mo');
});

test('partners can be registered and associated with programs with contribution amounts', function () {
    $partner = Partner::create([
        'sae_id' => $this->sae->id,
        'name' => 'Banque Ouest Africaine de Développement',
        'type' => PartnerType::BAILLEUR,
        'website' => 'https://www.boad.org',
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme Croissance PME',
        'status' => 'ACTIF',
    ]);

    $program->partners()->attach($partner->id, [
        'sae_id' => $this->sae->id,
        'role' => 'Partenaire Financier Stratégique',
        'contribution_amount' => 100000000.00,
        'currency' => 'XOF',
    ]);

    expect($program->partners)->toHaveCount(1)
        ->and($program->partners->first()->name)->toBe('Banque Ouest Africaine de Développement')
        ->and((float) $program->partners->first()->pivot->contribution_amount)->toBe(100000000.00)
        ->and($program->partners->first()->pivot->currency)->toBe('XOF');
});

test('collective coaching sessions track attendances for multiple projects', function () {
    $userCoach = User::factory()->create();
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $userCoach->id,
        'name' => 'Coach Master',
        'email' => $userCoach->email,
        'status' => 'ACTIF',
    ]);

    $user1 = User::factory()->create();
    $entrepreneur1 = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user1->id,
        'first_name' => 'Aïcha',
        'last_name' => 'Ba',
        'email' => $user1->email,
        'status' => 'ACTIVE',
    ]);
    $project1 = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur1->id,
        'name' => 'SolarTech Africa',
        'status' => ProjectStatus::ACTIF,
    ]);

    $user2 = User::factory()->create();
    $entrepreneur2 = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user2->id,
        'first_name' => 'Moussa',
        'last_name' => 'Touré',
        'email' => $user2->email,
        'status' => 'ACTIVE',
    ]);
    $project2 = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur2->id,
        'name' => 'AgriLogistics',
        'status' => ProjectStatus::ACTIF,
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Smart Energy Cohort',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 2026',
    ]);

    $session = CoachingSession::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'cohort_id' => $cohort->id,
        'date' => now()->addDays(2),
        'duration' => 120,
        'type' => CoachingSessionType::ATELIER,
        'theme' => 'Masterclass Financement & Valorisation',
        'status' => 'PLANIFIEE',
    ]);

    CoachingSessionAttendee::create([
        'coaching_session_id' => $session->id,
        'project_id' => $project1->id,
        'attended' => true,
        'notes' => 'Très bonne participation active.',
    ]);

    CoachingSessionAttendee::create([
        'coaching_session_id' => $session->id,
        'project_id' => $project2->id,
        'attended' => false,
        'notes' => 'Excusé pour déplacement commercial.',
    ]);

    expect($session->isCollective())->toBeTrue()
        ->and($session->attendees)->toHaveCount(2)
        ->and($session->attendees()->where('attended', true)->count())->toBe(1);
});

test('indicator entries track periodic progression over time', function () {
    $user = User::factory()->create();
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Fatou',
        'last_name' => 'Kane',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'HealthCare Plus',
        'status' => ProjectStatus::ACTIF,
    ]);

    $indicator = Indicator::create([
        'sae_id' => $this->sae->id,
        'name' => 'Chiffre d\'affaires mensuel',
        'category' => 'PORTEFEUILLE',
        'unit' => 'FCFA',
    ]);

    $entry1 = IndicatorEntry::create([
        'sae_id' => $this->sae->id,
        'indicator_id' => $indicator->id,
        'project_id' => $project->id,
        'period_on' => '2026-01-01',
        'value' => 500000.00,
        'comment' => 'Mois de démarrage',
    ]);

    $entry2 = IndicatorEntry::create([
        'sae_id' => $this->sae->id,
        'indicator_id' => $indicator->id,
        'project_id' => $project->id,
        'period_on' => '2026-02-01',
        'value' => 1200000.00,
        'comment' => 'Accélération forte',
    ]);

    expect($indicator->entries)->toHaveCount(2)
        ->and($project->indicatorEntries)->toHaveCount(2)
        ->and((float) $indicator->entries->first()->value)->toBe(1200000.00);
});

test('partners can be attached to program via relation manager attach action with pivot data', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Program Test Partners Attachment',
        'status' => 'ACTIF',
    ]);

    $partner = Partner::create([
        'sae_id' => $this->sae->id,
        'name' => 'Banque Ouest Africaine de Développement',
        'type' => PartnerType::BAILLEUR,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);

    Livewire::test(PartnersRelationManager::class, [
        'ownerRecord' => $program,
        'pageClass' => EditProgram::class,
    ])
        ->mountTableAction('attach')
        ->setTableActionData([
            'recordId' => $partner->id,
            'role' => 'Bailleur Principal',
            'contribution_amount' => 50000000,
            'currency' => 'XOF',
        ])
        ->callMountedTableAction()
        ->assertHasNoTableActionErrors();

    expect($program->fresh()->partners)->toHaveCount(1)
        ->and($program->fresh()->partners->first()->pivot->role)->toBe('Bailleur Principal')
        ->and((int) $program->fresh()->partners->first()->pivot->contribution_amount)->toBe(50000000);
});

test('attendance service can populate, export, and import attendance via excel csv for cohort sessions', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'ScaleUp AgriTech',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion Hiver 2026',
        'start_date' => '2026-10-01',
    ]);

    $entrepreneur1 = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'first_name' => 'Amadou',
        'last_name' => 'Traoré',
        'email' => 'amadou.traore@bioagri.africa',
    ]);

    $project1 = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur1->id,
        'name' => 'BioAgri Tech',
        'status' => ProjectStatus::ACTIF,
    ]);

    Enrollment::create([
        'sae_id' => $this->sae->id,
        'cohort_id' => $cohort->id,
        'project_id' => $project1->id,
        'entrepreneur_id' => $entrepreneur1->id,
        'status' => 'ACCEPTE',
    ]);

    $entrepreneur2 = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Diop',
        'email' => 'fatou.diop@solarirrigation.sn',
    ]);

    $project2 = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur2->id,
        'name' => 'SolarPump Sénégal',
        'status' => ProjectStatus::ACTIF,
    ]);

    Enrollment::create([
        'sae_id' => $this->sae->id,
        'cohort_id' => $cohort->id,
        'project_id' => $project2->id,
        'entrepreneur_id' => $entrepreneur2->id,
        'status' => 'ACCEPTE',
    ]);

    $userCoach = User::factory()->create();
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $userCoach->id,
        'name' => 'Coach Expert Test',
        'email' => $userCoach->email,
        'status' => 'ACTIF',
    ]);

    $session = CoachingSession::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'cohort_id' => $cohort->id,
        'theme' => 'Atelier Pitch & BMC',
        'type' => CoachingSessionType::ATELIER,
        'date' => '2026-10-12 09:00:00',
        'duration' => 120,
    ]);

    $service = app(AttendanceService::class);

    // 1. Peupler les présences depuis la cohorte
    $added = $service->populateFromCohort($session);
    expect($added)->toBe(2)
        ->and($session->fresh()->attendees)->toHaveCount(2);

    // 2. Exporter la feuille au format CSV/Excel
    $csv = $service->generateSessionAttendanceCsv($session);
    expect($csv)->toContain('ID_PROJET')
        ->and($csv)->toContain('BioAgri Tech')
        ->and($csv)->toContain('SolarPump Sénégal')
        ->and($csv)->toContain('amadou.traore@bioagri.africa')
        ->and(str_starts_with($csv, "\xEF\xBB\xBF"))->toBeTrue();

    // 3. Simuler la modification et le ré-import du CSV complété (Amadou Présent, Fatou Absente)
    $modifiedCsv = "\xEF\xBB\xBF\"ID_PROJET\";\"NOM_STARTUP\";\"ENTREPRENEUR\";\"EMAIL\";\"TELEPHONE\";\"PRESENCE (OUI/NON)\";\"OBSERVATIONS\";\"RETOUR_STARTUP\"\r\n"
        ."\"{$project1->id}\";\"BioAgri Tech\";\"Amadou Traoré\";\"amadou.traore@bioagri.africa\";\"\";\"OUI\";\"Excellente participation\";\"Intéressé par la session finance\"\r\n"
        ."\"{$project2->id}\";\"SolarPump Sénégal\";\"Fatou Diop\";\"fatou.diop@solarirrigation.sn\";\"\";\"NON\";\"Absence excusée pour salon\";\"\"\r\n";

    $stats = $service->importSessionAttendanceCsv($session, $modifiedCsv);

    expect($stats['total'])->toBe(2)
        ->and($stats['present'])->toBe(1)
        ->and($stats['absent'])->toBe(1)
        ->and($stats['updated'])->toBe(2);

    $att1 = CoachingSessionAttendee::where('coaching_session_id', $session->id)->where('project_id', $project1->id)->first();
    $att2 = CoachingSessionAttendee::where('coaching_session_id', $session->id)->where('project_id', $project2->id)->first();

    expect($att1->attended)->toBeTrue()
        ->and($att1->notes)->toBe('Excellente participation')
        ->and($att1->feedback)->toBe('Intéressé par la session finance')
        ->and($att2->attended)->toBeFalse()
        ->and($att2->notes)->toBe('Absence excusée pour salon');

    // 4. Générer la matrice globale de la cohorte
    $matrixCsv = $service->generateCohortAttendanceMatrixCsv($cohort);
    expect($matrixCsv)->toContain('BioAgri Tech')
        ->and($matrixCsv)->toContain('TOTAL_PRESENCES')
        ->and($matrixCsv)->toContain('TAUX_ASSIDUITE_%');

    // 5. Joindre la feuille d'émargement signée comme livrable officiel
    Storage::fake('public');
    Storage::disk('public')->put('coaching-sessions/attendance/feuille_signee.pdf', 'dummy pdf content');

    $user = User::factory()->create();
    $doc = $service->attachSignedDocument(
        session: $session,
        storedPath: 'coaching-sessions/attendance/feuille_signee.pdf',
        originalName: 'feuille_signee.pdf',
        title: 'Feuille d\'émargement officielle signée Promotion 2026',
        userId: $user->id
    );

    expect($doc->category)->toBe(DocumentCategory::LIVRABLE)
        ->and($doc->documentable_type)->toBe(CoachingSession::class)
        ->and($doc->documentable_id)->toBe($session->id)
        ->and($session->fresh()->documents)->toHaveCount(1);
});

test('attendees relation manager can populate cohort and attach signed sheet via livewire', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Prog Livewire Test',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Livewire 2026',
        'start_date' => '2026-10-01',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'first_name' => 'Saliou',
        'last_name' => 'Diallo',
        'email' => 'saliou.diallo@africadev.com',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'AfricaDev Logistics',
        'status' => ProjectStatus::ACTIF,
    ]);

    Enrollment::create([
        'sae_id' => $this->sae->id,
        'cohort_id' => $cohort->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => 'ACCEPTE',
    ]);

    $userCoach = User::factory()->create();
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $userCoach->id,
        'name' => 'Coach Saliou Coach',
        'email' => $userCoach->email,
        'status' => 'ACTIF',
    ]);

    $session = CoachingSession::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'cohort_id' => $cohort->id,
        'theme' => 'Atelier Stratégie Partenariats',
        'type' => CoachingSessionType::ATELIER,
        'date' => '2026-10-15 14:00:00',
        'duration' => 90,
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);

    Livewire::test(AttendeesRelationManager::class, [
        'ownerRecord' => $session,
        'pageClass' => EditCoachingSession::class,
    ])
        ->callTableAction('populateFromCohort')
        ->assertHasNoTableActionErrors();

    expect($session->fresh()->attendees)->toHaveCount(1)
        ->and($session->fresh()->attendees->first()->project_id)->toBe($project->id)
        ->and($session->fresh()->attendees->first()->attended)->toBeFalse();
});

test('cohort sessions relation manager mounts and renders properly with correct enum defaults', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Program Sessions Test',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Sessions Relation Manager',
        'start_date' => '2026-10-01',
    ]);

    $user = User::factory()->create();
    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);

    Livewire::test(CohortSessionsRelationManager::class, [
        'ownerRecord' => $cohort,
        'pageClass' => EditCohort::class,
    ])
        ->assertSuccessful()
        ->mountAction('create')
        ->assertHasNoActionErrors();
});
