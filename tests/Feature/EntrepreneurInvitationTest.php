<?php

use App\Enums\CohortStatus;
use App\Enums\EntrepreneurInvitationStatus;
use App\Enums\ProgramStatus;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\EntrepreneurInvitation;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
});

test('an entrepreneur can view the invitation landing page with valid token', function () {
    $sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Incubateur Dakar',
        'slug' => 'sae-incubateur-dakar',
    ]);

    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $sae->id,
        'email' => 'awa.fall@test.sn',
        'first_name' => 'Awa',
        'last_name' => 'Fall',
        'token' => 'token-test-entrepreneur-landing-123',
        'status' => EntrepreneurInvitationStatus::PENDING,
        'expires_at' => now()->addDays(14),
    ]);

    $response = $this->get(route('entrepreneur.invite', $invitation->token));

    $response->assertOk();
    $response->assertSee('Awa');
    $response->assertSee($sae->name);
});

test('an entrepreneur can accept an invitation without cohort', function () {
    $sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Incubateur Dakar',
        'slug' => 'sae-incubateur-dakar',
    ]);

    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $sae->id,
        'email' => 'entrepreneur.libre@test.sn',
        'first_name' => 'Moussa',
        'last_name' => 'Ndiaye',
        'token' => 'token-test-entrepreneur-no-cohort-456',
        'status' => EntrepreneurInvitationStatus::PENDING,
        'expires_at' => now()->addDays(14),
    ]);

    $response = $this->post(route('entrepreneur.invite.register', $invitation->token), [
        'first_name' => 'Moussa',
        'last_name' => 'Ndiaye',
        'phone' => '+221 77 111 22 33',
        'project_name' => 'AgriTech Sahel',
        'project_sector' => 'Agriculture',
        'project_description' => 'Plateforme de distribution d\'engrais bio.',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $user = User::where('email', 'entrepreneur.libre@test.sn')->first();
    expect($user)->not->toBeNull();

    $entrepreneur = Entrepreneur::where('email', 'entrepreneur.libre@test.sn')->first();
    expect($entrepreneur)->not->toBeNull();
    expect($entrepreneur->sae_id)->toBe($sae->id);

    $project = Project::where('entrepreneur_id', $entrepreneur->id)->first();
    expect($project)->not->toBeNull();
    expect($project->name)->toBe('AgriTech Sahel');

    // Aucune inscription en cohorte
    expect(Enrollment::where('entrepreneur_id', $entrepreneur->id)->count())->toBe(0);

    expect($invitation->fresh()->status)->toBe(EntrepreneurInvitationStatus::ACCEPTED);
});

test('an entrepreneur can accept an invitation linked to an active cohort', function () {
    $sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Incubateur Dakar',
        'slug' => 'sae-incubateur-dakar',
    ]);

    $program = Program::first() ?? Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Accélération 2026',
        'status' => ProgramStatus::ACTIF,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Promotion Printemps 2026',
        'capacity' => 15,
        'status' => CohortStatus::EN_COURS,
        'start_date' => now(),
    ]);

    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohort->id,
        'email' => 'laureat.cohorte@test.sn',
        'first_name' => 'Fatou',
        'last_name' => 'Sow',
        'project_name' => 'HealthConnect',
        'token' => 'token-test-entrepreneur-with-cohort-789',
        'status' => EntrepreneurInvitationStatus::PENDING,
        'expires_at' => now()->addDays(14),
    ]);

    $landing = $this->get(route('entrepreneur.invite', $invitation->token));
    $landing->assertOk();
    $landing->assertSee('Promotion Printemps 2026');

    $response = $this->post(route('entrepreneur.invite.register', $invitation->token), [
        'first_name' => 'Fatou',
        'last_name' => 'Sow',
        'phone' => '+221 78 555 66 77',
        'project_name' => 'HealthConnect',
        'password' => 'secretPassword123',
        'password_confirmation' => 'secretPassword123',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $entrepreneur = Entrepreneur::where('email', 'laureat.cohorte@test.sn')->first();
    expect($entrepreneur)->not->toBeNull();

    // Doit être automatiquement inscrit dans la cohorte
    $enrollment = Enrollment::where('entrepreneur_id', $entrepreneur->id)
        ->where('cohort_id', $cohort->id)
        ->first();

    expect($enrollment)->not->toBeNull();
    expect($enrollment->status)->toBe('ACTIF');
    expect($invitation->fresh()->status)->toBe(EntrepreneurInvitationStatus::ACCEPTED);
});

test('an existing entrepreneur can be directly assigned to an active cohort', function () {
    $sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Incubateur Dakar',
        'slug' => 'sae-incubateur-dakar',
    ]);

    $program = Program::first() ?? Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Accélération 2026',
        'status' => ProgramStatus::ACTIF,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte Fast Track',
        'capacity' => 20,
        'status' => CohortStatus::EN_COURS,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Ibrahima',
        'last_name' => 'Diallo',
        'email' => 'ibrahima.diallo@test.sn',
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Solaire Plus',
        'status' => 'ACTIF',
    ]);

    // Rattachement direct
    $enrollment = Enrollment::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => $project->id,
        'status' => 'ACTIF',
        'enrolled_at' => now(),
    ]);

    expect($enrollment)->not->toBeNull();
    expect($entrepreneur->fresh()->cohorts->pluck('id')->contains($cohort->id))->toBeTrue();
});

test('invalid or expired invitation token is rejected gracefully', function () {
    $response = $this->get(route('entrepreneur.invite', 'token-inexistant-xyz'));
    $response->assertOk();
    $response->assertSee('Lien d\'invitation', false);
});
