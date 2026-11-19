<?php

use App\Enums\FormType;
use App\Enums\ProgramStatus;
use App\Listeners\HandleCandidatureSubmission;
use App\Models\Form;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Support\FormSubmissionRedirectResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Packstub\FormBuilder\Events\SubmissionReceived;
use Packstub\FormBuilder\Models\FormSubmission;
use Packstub\FormBuilder\Submissions\SubmissionContext;

uses(RefreshDatabase::class);

test('isPortalUrl detects entrepreneur portal paths accurately', function () {
    expect(FormSubmissionRedirectResolver::isPortalUrl('https://hub-africa.com/portal'))->toBeTrue()
        ->and(FormSubmissionRedirectResolver::isPortalUrl('https://hub-africa.com/portal/cube/questionnaires/bilan'))->toBeTrue()
        ->and(FormSubmissionRedirectResolver::isPortalUrl('https://hub-africa.com/forms/candidature-2026'))->toBeFalse()
        ->and(FormSubmissionRedirectResolver::isPortalUrl('https://hub-africa.com/portal-info'))->toBeFalse()
        ->and(FormSubmissionRedirectResolver::isPortalUrl(null))->toBeFalse()
        ->and(FormSubmissionRedirectResolver::isPortalUrl(''))->toBeFalse();
});

test('resolve prioritizes project url, then portal url when outside portal, and skips when already on portal', function () {
    $candidatureRedirect = FormSubmissionRedirectResolver::resolve(
        projectUrl: 'https://hub-africa.com/portal/cube/projects/42',
        portalUrl: 'https://hub-africa.com/portal/cube',
        isAlreadyOnPortal: false,
    );

    expect($candidatureRedirect)->toBe([
        'url' => 'https://hub-africa.com/portal/cube/projects/42',
        'label' => 'Accéder à mon projet',
        'delaySeconds' => 3,
    ]);

    $otherFormOutsidePortal = FormSubmissionRedirectResolver::resolve(
        projectUrl: null,
        portalUrl: 'https://hub-africa.com/portal/cube',
        isAlreadyOnPortal: false,
    );

    expect($otherFormOutsidePortal)->toBe([
        'url' => 'https://hub-africa.com/portal/cube',
        'label' => 'Accéder à mon espace',
        'delaySeconds' => 3,
    ]);

    $otherFormInsidePortal = FormSubmissionRedirectResolver::resolve(
        projectUrl: null,
        portalUrl: 'https://hub-africa.com/portal/cube',
        isAlreadyOnPortal: true,
    );

    expect($otherFormInsidePortal)->toBeNull();

    $guestWithoutRedirect = FormSubmissionRedirectResolver::resolve(
        projectUrl: null,
        portalUrl: null,
        isAlreadyOnPortal: false,
    );

    expect($guestWithoutRedirect)->toBeNull();
});

test('candidature submission flashes redirect url to the created entrepreneur project', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'CUBE',
        'slug' => 'cube-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Cohorte 2026',
        'slug' => 'cohorte-2026-'.uniqid(),
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
    ]);

    $form = Form::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Candidature Standard',
        'slug' => 'candidature-standard-'.uniqid(),
        'type' => FormType::CANDIDATURE,
        'is_active' => true,
        'store_submissions' => true,
    ]);

    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'number' => 1,
        'data' => [
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'email' => 'awa.diallo@example.com',
            'project_name' => 'AgriTech Dakar',
            'sector' => 'Agriculture',
        ],
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        userAgent: 'Pest',
        sourceUrl: 'https://hub-africa.com/forms/'.$form->slug,
        channel: 'livewire',
        userId: null,
    );

    app(HandleCandidatureSubmission::class)->handle(new SubmissionReceived($form, $submission, $context));

    $project = Project::where('name', 'AgriTech Dakar')->first();

    expect($project)->not->toBeNull();

    $expectedUrl = route('filament.entrepreneur.resources.projects.view', [
        'tenant' => $sae->slug,
        'record' => $project->id,
    ]);

    expect(session()->get(FormSubmissionRedirectResolver::SESSION_PROJECT_URL_KEY))->toBe($expectedUrl);
});
