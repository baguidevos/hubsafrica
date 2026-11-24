<?php

use App\Enums\CohortStatus;
use App\Enums\FormAttemptStatus;
use App\Enums\FormType;
use App\Enums\ProgramStatus;
use App\Filament\Sae\Resources\FormAttempts\Pages\ListFormAttempts;
use App\Livewire\TrackedFormBuilderForm;
use App\Models\Cohort;
use App\Models\Form;
use App\Models\FormAttempt;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use App\Services\FormAttemptTracker;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Packstub\FormBuilder\Events\SpamDetected;
use Packstub\FormBuilder\Events\SubmissionReceived;
use Packstub\FormBuilder\Models\FormSubmission;
use Packstub\FormBuilder\Submissions\SubmissionContext;

function makeTrackableForm(Sae $sae, Program $program): Form
{
    return Form::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Candidature AgriPulse',
        'slug' => 'candidature-agripulse-'.uniqid(),
        'type' => FormType::CANDIDATURE,
        'is_active' => true,
        'fields' => [
            [
                'type' => 'section',
                'data' => [
                    'key' => 'identite',
                    'label' => 'Étape 1 : Identité',
                    'fields' => [
                        ['type' => 'text', 'data' => ['key' => 'project_name', 'label' => 'Nom du projet', 'required' => true]],
                        ['type' => 'textarea', 'data' => ['key' => 'pitch', 'label' => 'Pitch du projet', 'required' => true]],
                    ],
                ],
            ],
        ],
    ]);
}

test('FormAttemptTracker analyzes progress and formats readable validation errors', function () {
    $owner = User::factory()->create();
    $sae = Sae::create(['owner_id' => $owner->id, 'name' => 'SAE Tracker', 'slug' => 'sae-tracker-'.uniqid()]);
    $program = Program::create(['sae_id' => $sae->id, 'name' => 'AgriPulse', 'status' => ProgramStatus::ACTIF]);
    $form = makeTrackableForm($sae, $program);

    $tracker = app(FormAttemptTracker::class);

    $progress = $tracker->analyzeProgress($form, [
        'project_name' => 'Terroir Plus',
        'pitch' => '',
    ]);

    expect($progress)->toBe([
        'filled_count' => 1,
        'total_count' => 2,
        'last_filled_field' => 'Nom du projet',
        'last_step_label' => 'Étape 1 : Identité',
    ]);

    $summary = $tracker->summarizeValidationErrors([
        'data.pitch' => ['Le champ pitch est obligatoire.'],
    ], $form);

    expect($summary['summary'])->toBe('Pitch du projet : Le champ pitch est obligatoire.')
        ->and($summary['details'])->toBe(['Pitch du projet' => 'Le champ pitch est obligatoire.']);
});

test('mounting and failing validation on TrackedFormBuilderForm records attempt and exact validation reason', function () {
    $owner = User::factory()->create();
    $sae = Sae::create(['owner_id' => $owner->id, 'name' => 'SAE Livewire', 'slug' => 'sae-lw-'.uniqid()]);
    $program = Program::create(['sae_id' => $sae->id, 'name' => 'AgriPulse', 'status' => ProgramStatus::ACTIF]);
    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 1',
        'status' => CohortStatus::OUVERTE,
    ]);
    $form = makeTrackableForm($sae, $program);

    $candidate = User::factory()->create(['name' => 'Ahou Kouassi']);
    $this->actingAs($candidate);

    Livewire::test(TrackedFormBuilderForm::class, [
        'form' => $form,
        'values' => ['cohort_id' => (string) $cohort->id, 'project_name' => 'Terroir Plus'],
    ])
        ->call('submit')
        ->assertHasErrors();

    $attempt = FormAttempt::withoutGlobalScopes()
        ->where('form_id', $form->id)
        ->where('user_id', $candidate->id)
        ->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->status)->toBe(FormAttemptStatus::BLOQUE_VALIDATION)
        ->and($attempt->filled_fields_count)->toBe(1)
        ->and($attempt->total_fields_count)->toBe(2)
        ->and($attempt->last_filled_field)->toBe('Nom du projet')
        ->and($attempt->last_error_reason)->toContain('Pitch du projet');
});

test('SpamDetected and SubmissionReceived events update FormAttempt status accurately', function () {
    $owner = User::factory()->create();
    $sae = Sae::create(['owner_id' => $owner->id, 'name' => 'SAE Events', 'slug' => 'sae-events-'.uniqid()]);
    $program = Program::create(['sae_id' => $sae->id, 'name' => 'AgriPulse', 'status' => ProgramStatus::ACTIF]);
    $form = makeTrackableForm($sae, $program);
    $candidate = User::factory()->create();

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        userAgent: 'Pest',
        sourceUrl: 'https://hub-africa.com/forms/'.$form->slug,
        channel: 'livewire',
        userId: $candidate->id,
    );

    Event::dispatch(new SpamDetected($form, 'too_fast', ['project_name' => 'FastBot'], $context));

    $attempt = FormAttempt::withoutGlobalScopes()
        ->where('form_id', $form->id)
        ->where('user_id', $candidate->id)
        ->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->status)->toBe(FormAttemptStatus::BLOQUE_SPAM)
        ->and($attempt->last_error_reason)->toContain('trop rapidement');

    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'user_id' => $candidate->id,
        'number' => 1,
        'data' => ['project_name' => 'FastBot', 'pitch' => 'Mon pitch'],
    ]);

    Event::dispatch(new SubmissionReceived($form, $submission, $context));

    $fresh = $attempt->fresh();
    expect($fresh->status)->toBe(FormAttemptStatus::SOUMIS)
        ->and($fresh->submitted_at)->not->toBeNull()
        ->and($fresh->last_error_reason)->toBeNull();
});

test('SAE panel lists incomplete form attempts and exports them to CSV', function () {
    $owner = User::factory()->create();
    $sae = Sae::create(['owner_id' => $owner->id, 'name' => 'SAE Panel', 'slug' => 'sae-panel-'.uniqid()]);
    $program = Program::create(['sae_id' => $sae->id, 'name' => 'AgriPulse', 'status' => ProgramStatus::ACTIF]);
    $form = makeTrackableForm($sae, $program);

    $candidate = User::factory()->create(['name' => 'Koffi Abandon']);
    $attempt = FormAttempt::withoutGlobalScopes()->create([
        'sae_id' => $sae->id,
        'form_id' => $form->id,
        'program_id' => $program->id,
        'user_id' => $candidate->id,
        'status' => FormAttemptStatus::BLOQUE_VALIDATION,
        'last_step_label' => 'Étape 1 : Identité',
        'filled_fields_count' => 1,
        'total_fields_count' => 2,
        'last_filled_field' => 'Nom du projet',
        'last_error_reason' => 'Pitch du projet : Champ obligatoire.',
        'started_at' => now()->subMinutes(10),
        'last_activity_at' => now()->subMinutes(5),
    ]);

    $this->actingAs($owner);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    Livewire::test(ListFormAttempts::class)
        ->assertSuccessful()
        ->loadTable()
        ->assertCanSeeTableRecords([$attempt])
        ->callAction('exportFormAttempts')
        ->assertFileDownloaded();
});
