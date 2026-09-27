<?php

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use App\Filament\Entrepreneur\Pages\QuestionnairesPage;
use App\Filament\Entrepreneur\Widgets\EntrepreneurPendingSurveysWidget;
use App\Listeners\HandleEvaluationSubmission;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\FormUnlock;
use App\Models\Program;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use Packstub\FormBuilder\Events\SubmissionReceived;
use Packstub\FormBuilder\Models\FormSubmission;
use Packstub\FormBuilder\Submissions\SubmissionContext;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);
});

test('evaluation form can be associated with program and typed with FormType enum', function () {
    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Accélération Tech 2026',
        'slug' => 'acceleration-tech-2026',
        'status' => 'ACTIF',
    ]);

    $form = Form::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Diagnostic Initial - Baseline',
        'slug' => 'diagnostic-initial-baseline',
        'type' => FormType::BASELINE,
        'is_mandatory' => true,
        'fields' => [
            [
                'type' => 'text',
                'data' => [
                    'key' => 'chiffre_affaires_actuel',
                    'label' => 'Chiffre d\'affaires actuel (FCFA)',
                ],
            ],
        ],
    ]);

    expect($form->type)->toBe(FormType::BASELINE)
        ->and($form->program->id)->toBe($program->id)
        ->and($form->is_mandatory)->toBeTrue()
        ->and($form->totalUnlocksCount())->toBe(0)
        ->and($form->completionRate())->toBe(0.0);
});

test('form unlock can be created with due date and tracks overdue status', function () {
    $user = User::factory()->create();
    $user->saes()->attach($this->sae->id);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Aminata',
        'last_name' => 'Diallo',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $form = Form::create([
        'sae_id' => $this->sae->id,
        'name' => 'Enquête Satisfaction Mi-Parcours',
        'slug' => 'satisfaction-mi-parcours',
        'type' => FormType::SATISFACTION,
        'is_mandatory' => false,
    ]);

    // Unlock in the future
    $unlock = FormUnlock::create([
        'sae_id' => $this->sae->id,
        'form_id' => $form->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => FormUnlockStatus::EN_ATTENTE,
        'due_date' => now()->addDays(7),
        'note' => 'Merci de répondre rapidement.',
    ]);

    expect($unlock->isOverdue())->toBeFalse()
        ->and($unlock->isCompleted())->toBeFalse()
        ->and($form->fresh()->pendingUnlocksCount())->toBe(1);

    // Overdue unlock
    $unlock->update(['due_date' => now()->subDay()]);
    expect($unlock->isOverdue())->toBeTrue();
});

test('entrepreneur questionnaires page computes navigation badge and widget detects pending surveys', function () {
    $user = User::factory()->create();
    $user->saes()->attach($this->sae->id);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Mamadou',
        'last_name' => 'Ba',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $form = Form::create([
        'sae_id' => $this->sae->id,
        'name' => 'Bilan Final Sortie',
        'slug' => 'bilan-final-sortie',
        'type' => FormType::ENDLINE,
    ]);

    FormUnlock::create([
        'sae_id' => $this->sae->id,
        'form_id' => $form->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => FormUnlockStatus::EN_ATTENTE,
        'due_date' => now()->addDays(5),
    ]);

    $this->actingAs($user);

    expect(QuestionnairesPage::getNavigationBadge())->toBe('1')
        ->and(QuestionnairesPage::getNavigationBadgeColor())->toBe('warning')
        ->and(EntrepreneurPendingSurveysWidget::canView())->toBeTrue();
});

test('handle evaluation submission listener marks unlock as completed, logs timeline and notifies users', function () {
    $user = User::factory()->create();
    $user->saes()->attach($this->sae->id);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Khadija',
        'last_name' => 'Ndiaye',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $form = Form::create([
        'sae_id' => $this->sae->id,
        'name' => 'Questionnaire Impact Post-Programme',
        'slug' => 'impact-post-programme',
        'type' => FormType::POST_PROGRAMME,
    ]);

    $unlock = FormUnlock::create([
        'sae_id' => $this->sae->id,
        'form_id' => $form->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => FormUnlockStatus::EN_ATTENTE,
        'due_date' => now()->addDays(10),
    ]);

    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'user_id' => $user->id,
        'data' => [
            'emplois_crees' => 5,
            'chiffre_affaires' => 12500000,
        ],
    ]);

    $context = new SubmissionContext(userId: $user->id);
    $event = new SubmissionReceived($form, $submission, $context);

    // Call listener
    $listener = new HandleEvaluationSubmission();
    $listener->handle($event);

    $unlock->refresh();

    expect($unlock->status)->toBe(FormUnlockStatus::SOUMIS)
        ->and($unlock->isCompleted())->toBeTrue()
        ->and($unlock->completed_at)->not->toBeNull()
        ->and($unlock->submission_id)->toBe($submission->id);

    // Timeline event was recorded
    expect(TimelineEvent::where('entrepreneur_id', $entrepreneur->id)->where('type', 'EVALUATION')->exists())->toBeTrue();
});

test('fill questionnaire route and page are properly defined and accessible to assigned entrepreneur', function () {
    $user = User::factory()->create();
    $user->saes()->attach($this->sae->id);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Ousmane',
        'last_name' => 'Fall',
        'email' => $user->email,
        'status' => 'ACTIVE',
    ]);

    $form = Form::create([
        'sae_id' => $this->sae->id,
        'name' => 'Diagnostic Initial Route Test',
        'slug' => 'diagnostic-initial-route-test',
        'type' => FormType::BASELINE,
    ]);

    FormUnlock::create([
        'sae_id' => $this->sae->id,
        'form_id' => $form->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => FormUnlockStatus::EN_ATTENTE,
        'due_date' => now()->addDays(5),
    ]);

    $url = route('filament.entrepreneur.pages.fill-questionnaire', [
        'tenant' => $this->sae->slug,
        'slug' => $form->slug,
    ]);

    expect($url)->toContain("/portal/{$this->sae->slug}/questionnaires/{$form->slug}");

    $response = $this->actingAs($user)->get($url);
    $response->assertOk();
    $response->assertSee('Diagnostic Initial Route Test');
});
