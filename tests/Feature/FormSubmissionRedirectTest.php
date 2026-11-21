<?php

use App\Enums\EntrepreneurStatus;
use App\Enums\FormType;
use App\Enums\Gender;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Listeners\HandleCandidatureSubmission;
use App\Models\Entrepreneur;
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

test('resolveForCandidature enforces project completion first, then profile completion, then portal', function () {
    $incompleteProject = FormSubmissionRedirectResolver::resolveForCandidature(
        isProjectComplete: false,
        isProfileComplete: false,
        projectEditUrl: 'https://hub-africa.com/portal/cube/projects/42/edit',
        profileUrl: 'https://hub-africa.com/portal/cube/profile-page',
        portalUrl: '/portal',
    );

    expect($incompleteProject['url'])->toBe('https://hub-africa.com/portal/cube/projects/42/edit')
        ->and($incompleteProject['forced'])->toBeTrue();

    $incompleteProfile = FormSubmissionRedirectResolver::resolveForCandidature(
        isProjectComplete: true,
        isProfileComplete: false,
        projectEditUrl: 'https://hub-africa.com/portal/cube/projects/42/edit',
        profileUrl: 'https://hub-africa.com/portal/cube/profile-page',
        portalUrl: '/portal',
    );

    expect($incompleteProfile['url'])->toBe('https://hub-africa.com/portal/cube/profile-page')
        ->and($incompleteProfile['forced'])->toBeTrue();

    $allComplete = FormSubmissionRedirectResolver::resolveForCandidature(
        isProjectComplete: true,
        isProfileComplete: true,
        projectEditUrl: 'https://hub-africa.com/portal/cube/projects/42/edit',
        profileUrl: 'https://hub-africa.com/portal/cube/profile-page',
        portalUrl: '/portal',
    );

    expect($allComplete['url'])->toBe('/portal')
        ->and($allComplete['forced'])->toBeFalse();
});

test('new forms default to /portal redirect url and require login', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'CUBE',
        'slug' => 'cube-'.uniqid(),
    ]);

    $form = Form::create([
        'sae_id' => $sae->id,
        'name' => 'Formulaire Test',
        'slug' => 'formulaire-test-'.uniqid(),
        'type' => FormType::CANDIDATURE,
    ]);

    expect($form->redirect_url)->toBe('/portal')
        ->and($form->requiresLogin())->toBeTrue();
});

test('guests are redirected to login before accessing programme application or standalone forms', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'CUBE',
        'slug' => 'cube-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'AgriPulse',
        'slug' => 'agripulse-'.uniqid(),
        'status' => ProgramStatus::OUVERT_CANDIDATURES,
    ]);

    $form = Form::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Candidature AgriPulse',
        'slug' => 'candidature-agripulse-'.uniqid(),
        'type' => FormType::CANDIDATURE,
    ]);

    $loginUrl = route('filament.entrepreneur.auth.login');

    $this->get(route('public.programme.candidater', ['id' => $program->id]))
        ->assertRedirect($loginUrl);

    $this->get('/forms/'.$form->slug)
        ->assertRedirect($loginUrl);
});

test('candidature submission uses authenticated user email and forces redirect to project edit when project is incomplete', function () {
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
        'name' => 'Candidature Personnalisée',
        'slug' => 'candidature-custom-'.uniqid(),
        'type' => FormType::CANDIDATURE,
    ]);

    $candidate = User::factory()->create([
        'name' => 'Awa Diallo',
        'email' => 'awa.auth@example.com',
    ]);
    $this->actingAs($candidate);

    // Formulaire personnalisé sans champ "email"
    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'user_id' => $candidate->id,
        'number' => 1,
        'data' => [
            'project_name' => 'AgriTech Dakar',
            'sector' => 'Agriculture',
        ],
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        userAgent: 'Pest',
        sourceUrl: 'https://hub-africa.com/forms/'.$form->slug,
        channel: 'livewire',
        userId: $candidate->id,
    );

    app(HandleCandidatureSubmission::class)->handle(new SubmissionReceived($form, $submission, $context));

    $entrepreneur = Entrepreneur::where('user_id', $candidate->id)->first();
    $project = Project::where('name', 'AgriTech Dakar')->first();

    expect($entrepreneur)->not->toBeNull()
        ->and($entrepreneur->email)->toBe('awa.auth@example.com')
        ->and($project)->not->toBeNull();

    $expectedEditUrl = route('filament.entrepreneur.resources.projects.edit', [
        'tenant' => $sae->slug,
        'record' => $project->id,
    ]);

    expect($form->redirect_url)->toBe($expectedEditUrl)
        ->and(session()->get(FormSubmissionRedirectResolver::SESSION_PROJECT_URL_KEY))->toBe($expectedEditUrl);
});

test('candidature submission forces redirect to profile when project is 100% complete but profile is incomplete', function () {
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
    ]);

    $candidate = User::factory()->create(['email' => 'koffi@example.com']);
    $this->actingAs($candidate);

    $entrepreneur = Entrepreneur::create([
        'user_id' => $candidate->id,
        'sae_id' => $sae->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => $candidate->email,
        'status' => EntrepreneurStatus::ACTIVE,
        // Champs profil manquants : phone, gender, birth_date, country, etc.
    ]);

    Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Terroir Plus',
        'sector' => 'Agriculture',
        'territory' => 'Daloa',
        'team_size' => 4,
        'target' => 'Ménages et grossistes',
        'market' => 'Haut-Sassandra',
        'value_proposition' => 'Manioc frais toute l\'année',
        'problem' => 'Approvisionnement irrégulier',
        'solution' => 'Calendrier de récolte structuré',
        'product_service' => 'Manioc frais et transformé',
        'pitch' => 'Valoriser le terroir local',
        'description' => 'Projet agricole structuré à Daloa',
        'status' => ProjectStatus::ACTIF,
    ]);

    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'user_id' => $candidate->id,
        'number' => 1,
        'data' => [
            'project_name' => 'Terroir Plus',
        ],
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        userAgent: 'Pest',
        sourceUrl: 'https://hub-africa.com/forms/'.$form->slug,
        channel: 'livewire',
        userId: $candidate->id,
    );

    app(HandleCandidatureSubmission::class)->handle(new SubmissionReceived($form, $submission, $context));

    $expectedProfileUrl = route('filament.entrepreneur.pages.profile-page', [
        'tenant' => $sae->slug,
    ]);

    expect($form->redirect_url)->toBe($expectedProfileUrl)
        ->and(session()->get(FormSubmissionRedirectResolver::SESSION_PROJECT_URL_KEY))->toBe($expectedProfileUrl);
});

test('candidature submission lets entrepreneur pass to portal when both project and profile are 100% complete', function () {
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
    ]);

    $candidate = User::factory()->create(['email' => 'complete@example.com']);
    $this->actingAs($candidate);

    $entrepreneur = Entrepreneur::create([
        'user_id' => $candidate->id,
        'sae_id' => $sae->id,
        'first_name' => 'Ahou',
        'last_name' => 'Kouassi',
        'email' => $candidate->email,
        'phone' => '+2250700000000',
        'gender' => Gender::F,
        'birth_date' => '1995-05-10',
        'country' => 'Côte d\'Ivoire',
        'territory' => 'Daloa',
        'education_level' => 'Master',
        'profession' => 'Agronome',
        'professional_status' => 'Entrepreneure',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Terroir Plus',
        'sector' => 'Agriculture',
        'territory' => 'Daloa',
        'team_size' => 4,
        'target' => 'Ménages et grossistes',
        'market' => 'Haut-Sassandra',
        'value_proposition' => 'Manioc frais toute l\'année',
        'problem' => 'Approvisionnement irrégulier',
        'solution' => 'Calendrier de récolte structuré',
        'product_service' => 'Manioc frais et transformé',
        'pitch' => 'Valoriser le terroir local',
        'description' => 'Projet agricole structuré à Daloa',
        'status' => ProjectStatus::ACTIF,
    ]);

    $submission = FormSubmission::create([
        'form_id' => $form->id,
        'user_id' => $candidate->id,
        'number' => 1,
        'data' => [
            'project_name' => 'Terroir Plus',
        ],
    ]);

    $context = new SubmissionContext(
        ip: '127.0.0.1',
        userAgent: 'Pest',
        sourceUrl: 'https://hub-africa.com/forms/'.$form->slug,
        channel: 'livewire',
        userId: $candidate->id,
    );

    app(HandleCandidatureSubmission::class)->handle(new SubmissionReceived($form, $submission, $context));

    expect($form->redirect_url)->toBe('/portal')
        ->and(session()->get(FormSubmissionRedirectResolver::SESSION_PROJECT_URL_KEY))->toBe('/portal');
});
