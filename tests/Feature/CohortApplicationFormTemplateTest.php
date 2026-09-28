<?php

use App\Enums\CohortStatus;
use App\Enums\FormType;
use App\Enums\ProgramStatus;
use App\Livewire\Public\ProgramApplicationWizard;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Form;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use App\Services\ApplicationFormTemplateService;
use Livewire\Livewire;

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

test('le template standardise officiel hub africa est cree avec les bons attributs', function () {
    expect($this->standardTemplate)->not->toBeNull()
        ->and($this->standardTemplate->is_standard)->toBeTrue()
        ->and($this->standardTemplate->is_template)->toBeTrue()
        ->and($this->standardTemplate->type)->toEqual(FormType::CANDIDATURE)
        ->and($this->standardTemplate->sae_id)->toBeNull();

    expect($this->standardTemplate->fields)->toBeArray()
        ->and(count($this->standardTemplate->fields))->toBeGreaterThan(5);
});

test('une cohorte est liee a son formulaire de candidature obligatoire', function () {
    expect($this->cohort->application_form_id)->toEqual($this->standardTemplate->id);
    expect($this->cohort->applicationForm)->not->toBeNull();
    expect($this->cohort->applicationForm->id)->toEqual($this->standardTemplate->id);
});

test('une sae peut dupliquer le template standard en tant que template personnalise de sa structure', function () {
    $customForm = $this->templateService->duplicateAsSaeTemplate(
        $this->standardTemplate,
        $this->sae->id,
        'Template Spécifique Teranga'
    );

    expect($customForm)->not->toBeNull()
        ->and($customForm->sae_id)->toEqual($this->sae->id)
        ->and($customForm->is_standard)->toBeFalse()
        ->and($customForm->is_template)->toBeTrue()
        ->and($customForm->name)->toEqual('Template Spécifique Teranga');

    // Vérifier la liste des templates disponibles pour cette SAE
    $templates = $this->templateService->getAvailableTemplatesForSae($this->sae->id);
    expect($templates->pluck('id'))->toContain($this->standardTemplate->id)
        ->and($templates->pluck('id'))->toContain($customForm->id);
});

test('un candidat peut postuler a une cohorte avec des questions specifiques et enregistrer ses reponses', function () {
    // Créer un template spécifique pour la SAE avec une question additionnelle
    $saeTemplate = Form::create([
        'sae_id' => $this->sae->id,
        'name' => 'Dossier Teranga avec Question Climat',
        'slug' => 'dossier-teranga-climat-'.uniqid(),
        'type' => FormType::CANDIDATURE,
        'is_template' => true,
        'is_standard' => false,
        'is_active' => true,
        'fields' => array_merge($this->templateService->getStandardFieldsDefinition(), [
            [
                'type' => 'text',
                'data' => [
                    'key' => 'impact_carbone',
                    'label' => 'Quel est l\'impact carbone de votre solution ?',
                    'required' => true,
                    'placeholder' => 'Ex: Réduction de 20 tonnes CO2/an',
                ],
            ],
        ]),
    ]);

    // Associer ce template personnalisé à la cohorte
    $this->cohort->update(['application_form_id' => $saeTemplate->id]);

    $user = User::factory()->create([
        'name' => 'Fatou Faye',
        'email' => 'fatou.faye@startup.sn',
    ]);

    Livewire::actingAs($user)
        ->test(ProgramApplicationWizard::class, [
            'program' => $this->program,
            'cohortId' => $this->cohort->id,
        ])
        ->assertSet('applicationForm.id', $saeTemplate->id)
        ->set('first_name', 'Fatou')
        ->set('last_name', 'Faye')
        ->set('email', 'fatou.faye@startup.sn')
        ->set('phone', '+221 77 123 45 67')
        ->set('gender', 'F')
        ->set('city', 'Dakar')
        ->call('goToStep', 2)
        ->assertSet('currentStep', 2)
        ->set('project_name', 'Solaire Sahel')
        ->set('sector', 'CleanTech / Énergie & Climat')
        ->set('pitch', 'Fourniture de kits solaires pour zones rurales non raccordées.')
        ->set('team_size', 4)
        ->call('goToStep', 3)
        ->assertSet('currentStep', 3)
        ->set('motivation', 'Bénéficier du réseau de mentors et partenaires de la SAE.')
        ->set('needs', ['Accès aux marchés & Ventes', 'Levée de fonds & Financement'])
        ->set('custom_answers.impact_carbone', 'Réduction de 50 tonnes de CO2 par an grâce aux mini-réseaux.')
        ->call('submitApplication')
        ->assertHasNoErrors()
        ->assertSee('votre dossier a été transmis');

    // Vérification de la candidature en BDD
    $application = Application::where('email', 'fatou.faye@startup.sn')
        ->orWhere('entrepreneur_id', fn ($q) => $q->select('id')->from('entrepreneurs')->where('email', 'fatou.faye@startup.sn'))
        ->first();

    expect($application)->not->toBeNull();
    expect($application->form_id)->toEqual($saeTemplate->id);
    expect($application->cohort_id)->toEqual($this->cohort->id);
    expect($application->answers)->toBeArray();
    expect($application->answers['impact_carbone'])->toContain('50 tonnes');
    expect($application->comment)->toContain('impact carbone');
});
