<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Mail\CompleteProjectCohortInviteMail;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Support\EntrepreneurProjectReminderService;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('header action reminds candidates without project as CANDIDAT without creating enrollment', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Candidats',
        'slug' => 'sae-candidats-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Incube',
        'slug' => 'prog-incube-'.uniqid(),
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 2026',
        'status' => CohortStatus::OUVERTE,
    ]);

    $candidateWithoutProject = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Awa',
        'last_name' => 'Diop',
        'email' => 'awa@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $candidateWithoutProject->id,
        'status' => ApplicationStatus::EN_ATTENTE,
        'submitted_at' => now(),
    ]);

    $candidateWithProject = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Moussa',
        'last_name' => 'Fall',
        'email' => 'moussa@example.com',
        'status' => EntrepreneurStatus::CANDIDAT,
    ]);

    $existingProject = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $candidateWithProject->id,
        'name' => 'Projet Existant',
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $candidateWithProject->id,
        'project_id' => $existingProject->id,
        'status' => ApplicationStatus::EN_ATTENTE,
        'submitted_at' => now(),
    ]);

    $directWithoutProject = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Kossi',
        'last_name' => 'Agbo',
        'email' => 'kossi@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->actingAs($owner);
    Filament::setCurrentPanel('sae');
    Filament::setTenant($sae);

    Livewire::test(ListEntrepreneurs::class)
        ->callAction('remind_candidates_without_project', [
            'entrepreneur_ids' => [$candidateWithoutProject->id],
            'subject' => 'Action obligatoire — Complétez votre projet',
            'custom_message' => '<p>Merci de <strong>finaliser</strong> votre projet.</p>',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    expect($candidateWithoutProject->fresh()->status)->toBe(EntrepreneurStatus::CANDIDAT);
    expect(Enrollment::where('entrepreneur_id', $candidateWithoutProject->id)->exists())->toBeFalse();

    Mail::assertQueued(CompleteProjectCohortInviteMail::class, function (CompleteProjectCohortInviteMail $mail) use ($candidateWithoutProject, $cohort): bool {
        return $mail->hasTo($candidateWithoutProject->email)
            && $mail->cohort?->id === $cohort->id
            && $mail->isCohortEnrollment === false
            && $mail->customSubject === 'Action obligatoire — Complétez votre projet';
    });

    Mail::assertNotQueued(CompleteProjectCohortInviteMail::class, fn (CompleteProjectCohortInviteMail $mail): bool => $mail->hasTo($directWithoutProject->email));
});

test('header action reminds direct entrepreneurs without project with optional cohort and detects linked program', function () {
    Mail::fake();

    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Directs',
        'slug' => 'sae-directs-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Boost',
        'slug' => 'prog-boost-'.uniqid(),
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Boost 1',
        'status' => CohortStatus::EN_COURS,
    ]);

    $linkedEntrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Kodjo',
        'last_name' => 'Mensah',
        'email' => 'kodjo@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    Enrollment::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $linkedEntrepreneur->id,
        'status' => 'ACTIF',
        'enrolled_at' => now(),
    ]);

    $unlinkedEntrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Salma',
        'last_name' => 'Bennani',
        'email' => 'salma@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $service = app(EntrepreneurProjectReminderService::class);
    $detected = $service->directEntrepreneursWithoutProjectQuery($sae->id)->get()->keyBy('id');

    expect($service->formatDirectEntrepreneurOptionLabel($detected[$linkedEntrepreneur->id]))
        ->toContain('Programme Boost (Cohorte Boost 1)');
    expect($service->formatDirectEntrepreneurOptionLabel($detected[$unlinkedEntrepreneur->id]))
        ->toContain('Aucun programme lié');

    $this->actingAs($owner);
    Filament::setCurrentPanel('sae');
    Filament::setTenant($sae);

    Livewire::test(ListEntrepreneurs::class)
        ->callAction('remind_direct_without_project', [
            'entrepreneur_ids' => [$unlinkedEntrepreneur->id],
            'cohort_id' => null,
            'subject' => '[Action requise] Renseignez votre projet',
            'custom_message' => '<p>Veuillez renseigner votre fiche projet.</p>',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    expect(Enrollment::where('entrepreneur_id', $unlinkedEntrepreneur->id)->exists())->toBeFalse();

    Mail::assertQueued(CompleteProjectCohortInviteMail::class, function (CompleteProjectCohortInviteMail $mail) use ($unlinkedEntrepreneur): bool {
        return $mail->hasTo($unlinkedEntrepreneur->email)
            && $mail->cohort === null
            && $mail->isCohortEnrollment === false;
    });
});

test('creating a project automatically links pending enrollments and applications without project_id', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Sync',
        'slug' => 'sae-sync-'.uniqid(),
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Sync',
        'slug' => 'prog-sync-'.uniqid(),
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Sync',
        'status' => CohortStatus::EN_COURS,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Fatima',
        'last_name' => 'Toure',
        'email' => 'fatima@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $enrollment = Enrollment::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => null,
        'status' => 'ACTIF',
        'enrolled_at' => now(),
    ]);

    $application = Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => null,
        'status' => ApplicationStatus::EN_ATTENTE,
        'submitted_at' => now(),
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'AgriTech Togo',
    ]);

    expect($enrollment->fresh()->project_id)->toBe($project->id);
    expect($application->fresh()->project_id)->toBe($project->id);
});

test('complete project invite mail renders rich text html properly', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'Cube',
        'slug' => 'cube-'.uniqid(),
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Yao',
        'last_name' => 'Koffi',
        'email' => 'yao@example.com',
    ]);

    $mailable = new CompleteProjectCohortInviteMail(
        entrepreneur: $entrepreneur,
        cohort: null,
        sae: $sae,
        customMessage: '<p>Paragraphe 1</p><ul><li>Point <strong>important</strong></li></ul>',
        customSubject: '[Action requise] Complétez votre projet',
        isCohortEnrollment: false,
    );

    $rendered = $mailable->render();

    expect($rendered)
        ->toContain('<p>Paragraphe 1</p>')
        ->toContain('<li>Point <strong>important</strong></li>')
        ->not->toContain('&lt;p&gt;');
});

test('direct entrepreneurs without project can be sorted by creation date and exported to csv', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Tri Export',
        'slug' => 'sae-tri-export-'.uniqid(),
    ]);

    $older = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Ancien',
        'last_name' => 'Porteur',
        'email' => 'ancien@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $older->forceFill(['created_at' => now()->subDays(10)])->saveQuietly();

    $newer = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Nouveau',
        'last_name' => 'Porteur',
        'email' => 'nouveau@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $newer->forceFill(['created_at' => now()->subDay()])->saveQuietly();

    $service = app(EntrepreneurProjectReminderService::class);

    $descIds = $service->directEntrepreneursWithoutProjectQuery($sae->id, 'created_at_desc')->pluck('id')->all();
    $ascIds = $service->directEntrepreneursWithoutProjectQuery($sae->id, 'created_at_asc')->pluck('id')->all();

    expect($descIds)->toBe([$newer->id, $older->id])
        ->and($ascIds)->toBe([$older->id, $newer->id])
        ->and($service->formatDirectEntrepreneurOptionLabel($newer))->toContain('Inscrit le ');

    $response = $service->streamDirectWithoutProjectCsv($sae->id, $sae->slug);
    ob_start();
    $response->sendContent();
    $csv = (string) ob_get_clean();

    expect($csv)
        ->toContain('nouveau@example.com')
        ->toContain('ancien@example.com')
        ->and(strpos($csv, 'nouveau@example.com'))->toBeLessThan(strpos($csv, 'ancien@example.com'));
});
