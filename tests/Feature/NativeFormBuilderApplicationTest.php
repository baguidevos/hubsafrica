<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\Gender;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Services\ApplicationFormTemplateService;
use Illuminate\Support\Facades\Event;
use Packstub\FormBuilder\Events\SubmissionReceived;
use Packstub\FormBuilder\Models\FormSubmission;
use Packstub\FormBuilder\Submissions\SubmissionContext;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Lab',
        'slug' => 'sae-teranga-lab',
    ]);

    $this->templateService = app(ApplicationFormTemplateService::class);
    $this->standardTemplate = $this->templateService->getOrCreateStandardTemplate();

    $this->program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Incubation Numérique Afrique',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 20,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);

    $this->cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'application_form_id' => $this->standardTemplate->id,
        'name' => 'Cohorte Tech Alpha 2026',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 15,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);
});

test('la page publique de candidature affiche le composant livewire form-builder natif', function () {
    $response = $this->get(route('public.programme.candidater', [
        'id' => $this->program->id,
        'cohort' => $this->cohort->id,
    ]));

    $response->assertStatus(200);
    $response->assertSee('Dossier de Candidature en Ligne');
    $response->assertSee('Cohorte Tech Alpha 2026');
    $response->assertSeeLivewire('form-builder');
});

test('la structure json du template standardisé contient des sections conformes au moteur packstub form builder', function () {
    $form = $this->standardTemplate;

    expect($form->hasSections())->toBeTrue();
    expect($form->isWizard())->toBeTrue();
    expect($form->sections())->toHaveCount(3);

    $sectionKeys = $form->sections()->map(fn ($s) => $s->key)->all();
    expect($sectionKeys)->toEqual(['profil_porteur', 'startup_projet', 'motivation_pieces']);

    $sectionLabels = $form->sections()->map(fn ($s) => $s->label)->all();
    expect($sectionLabels[0])->toContain('Profil du Porteur');
    expect($sectionLabels[1])->toContain('Projet & Startup');
    expect($sectionLabels[2])->toContain('Motivation & Pièces');

    $inputKeys = $form->inputFields()->keys()->all();
    expect($inputKeys)->toContain('first_name', 'last_name', 'email', 'phone', 'gender', 'city');
    expect($inputKeys)->toContain('project_name', 'sector', 'team_size', 'pitch', 'website');
    expect($inputKeys)->toContain('motivation', 'needs', 'pitch_deck', 'program_id', 'cohort_id');
});

test('le listener de candidature traite automatiquement une soumission du form builder natif', function () {
    $submissionData = [
        'first_name' => 'Moussa',
        'last_name' => 'Ndiaye',
        'email' => 'moussa.ndiaye@agritech.sn',
        'phone' => '+221 78 999 88 77',
        'gender' => 'M',
        'city' => 'Thiès',
        'project_name' => 'AgriDrone Sahel',
        'sector' => 'AgriTech',
        'pitch' => 'Drones pour la surveillance hydrique des cultures sahéliennes.',
        'team_size' => 3,
        'website' => 'https://agridrone.sn',
        'motivation' => 'Accéder aux mentors experts en agronomie et levée de fonds.',
        'needs' => ['Mentorat technique', 'Financement'],
        'program_id' => (string) $this->program->id,
        'cohort_id' => (string) $this->cohort->id,
        'chiffre_affaires_annuel' => '15000000 FCFA',
    ];

    $submission = FormSubmission::create([
        'form_id' => $this->standardTemplate->id,
        'data' => $submissionData,
        'channel' => 'web',
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        sourceUrl: "http://localhost/programmes/{$this->program->id}/candidater?cohort={$this->cohort->id}",
    );

    // Déclenchement de l'événement natif de soumission Packstub
    Event::dispatch(new SubmissionReceived($this->standardTemplate, $submission, $context));

    // 1. Vérification de la création de l'utilisateur
    $user = User::where('email', 'moussa.ndiaye@agritech.sn')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toEqual('Moussa Ndiaye');

    // 2. Vérification de l'entrepreneur
    $entrepreneur = Entrepreneur::where('user_id', $user->id)->first();
    expect($entrepreneur)->not->toBeNull();
    expect($entrepreneur->sae_id)->toEqual($this->sae->id);
    expect($entrepreneur->gender)->toEqual(Gender::M);
    expect($entrepreneur->city)->toEqual('Thiès');

    // 3. Vérification du projet créé
    $project = Project::where('entrepreneur_id', $entrepreneur->id)->first();
    expect($project)->not->toBeNull();
    expect($project->name)->toEqual('AgriDrone Sahel');
    expect($project->sector)->toEqual('AgriTech');
    expect($project->status)->toEqual(ProjectStatus::ACTIF);

    // 4. Vérification de la candidature officielle créée
    $application = Application::where('entrepreneur_id', $entrepreneur->id)->first();
    expect($application)->not->toBeNull();
    expect($application->program_id)->toEqual($this->program->id);
    expect($application->cohort_id)->toEqual($this->cohort->id);
    expect($application->form_id)->toEqual($this->standardTemplate->id);
    expect($application->status)->toEqual(ApplicationStatus::SOUMISE);

    // Vérification des réponses personnalisées et du commentaire
    expect($application->answers)->toBeArray();
    expect($application->answers)->toHaveKey('chiffre_affaires_annuel');
    expect($application->answers['chiffre_affaires_annuel'])->toEqual('15000000 FCFA');
    expect($application->comment)->toContain('Accéder aux mentors experts');

    // 5. Vérification du jalon Timeline
    $timeline = TimelineEvent::where('project_id', $project->id)->first();
    expect($timeline)->not->toBeNull();
    expect($timeline->title)->toContain('Candidature officielle déposée');
});

test('le listener réutilise le compte existant pour un entrepreneur authentifié', function () {
    $existingUser = User::factory()->create([
        'name' => 'Aïssatou Ba',
        'email' => 'aissatou.ba@health.sn',
    ]);

    $existingEntrepreneur = Entrepreneur::create([
        'user_id' => $existingUser->id,
        'sae_id' => $this->sae->id,
        'first_name' => 'Aïssatou',
        'last_name' => 'Ba',
        'email' => $existingUser->email,
        'phone' => '+221 77 555 44 33',
        'gender' => Gender::F,
        'city' => 'Dakar',
        'status' => 'ACTIVE',
    ]);

    $submissionData = [
        'first_name' => 'Aïssatou',
        'last_name' => 'Ba',
        'email' => 'aissatou.ba@health.sn',
        'project_name' => 'HealthCare Mobile',
        'sector' => 'Santé',
        'pitch' => 'Application de télémédecine.',
        'motivation' => 'Développer notre couverture régionale.',
        'program_id' => (string) $this->program->id,
        'cohort_id' => (string) $this->cohort->id,
    ];

    $submission = FormSubmission::create([
        'form_id' => $this->standardTemplate->id,
        'data' => $submissionData,
        'channel' => 'web',
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        sourceUrl: "http://localhost/programmes/{$this->program->id}/candidater?cohort={$this->cohort->id}",
        userId: $existingUser->id,
    );

    Event::dispatch(new SubmissionReceived($this->standardTemplate, $submission, $context));

    // Vérifier qu'aucun nouvel utilisateur n'a été créé
    expect(User::where('email', 'aissatou.ba@health.sn')->count())->toEqual(1);
    expect(Entrepreneur::where('user_id', $existingUser->id)->count())->toEqual(1);

    // Vérifier l'application
    $app = Application::where('entrepreneur_id', $existingEntrepreneur->id)->first();
    expect($app)->not->toBeNull();
    expect($app->project->name)->toEqual('HealthCare Mobile');
});
