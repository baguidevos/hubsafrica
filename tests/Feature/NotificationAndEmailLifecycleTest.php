<?php

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Enums\CoachJoinRequestStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\ProjectStatus;
use App\Mail\ApplicationAdmittedMail;
use App\Mail\ApplicationDecisionMail;
use App\Mail\CoachAssignedStartupMail;
use App\Mail\CoachDirectInvitationMail;
use App\Mail\CoachingSessionScheduledMail;
use App\Mail\CoachJoinRequestAcceptedMail;
use App\Mail\CoachJoinRequestReceivedMail;
use App\Mail\CoachWelcomeMail;
use App\Mail\DeliverableStatusMail;
use App\Mail\FounderWelcomeMail;
use App\Mail\SaeManagerWelcomeMail;
use App\Mail\StartupAssignedToCoachMail;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\CoachInvitation;
use App\Models\CoachJoinRequest;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

beforeEach(function () {
    Mail::fake();

    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Incubateur Test',
        'slug' => 'sae-incubateur-test-'.Str::random(6),
        'status' => 'ACTIVE',
    ]);
});

test('application admission triggers ApplicationAdmittedMail', function () {
    $user = User::factory()->create(['email' => 'founder@test.com']);
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => 'founder@test.com',
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme Accélération FinTech',
        'status' => 'ACTIF',
    ]);

    $cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 2026',
        'status' => 'OUVERTE',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'PayFast Africa',
        'status' => ProjectStatus::ACTIF,
    ]);

    $application = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => $project->id,
        'status' => ApplicationStatus::SOUMISE,
        'submitted_at' => now(),
    ]);

    Mail::assertNothingQueued();

    // Passer le statut à ACCEPTEE
    $application->update(['status' => ApplicationStatus::ACCEPTEE]);

    Mail::assertQueued(ApplicationAdmittedMail::class, function ($mail) use ($application) {
        return $mail->hasTo('founder@test.com') && $mail->application->id === $application->id;
    });
});

test('application rejection triggers ApplicationDecisionMail', function () {
    $user = User::factory()->create(['email' => 'founder2@test.com']);
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Marc',
        'last_name' => 'Kone',
        'email' => 'founder2@test.com',
        'status' => 'ACTIVE',
    ]);

    $program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme BioTech',
        'status' => 'ACTIF',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'BioHealth CI',
        'status' => ProjectStatus::ACTIF,
    ]);

    $application = Application::create([
        'sae_id' => $this->sae->id,
        'program_id' => $program->id,
        'entrepreneur_id' => $entrepreneur->id,
        'project_id' => $project->id,
        'status' => ApplicationStatus::EN_EXAMEN,
    ]);

    $application->update([
        'status' => ApplicationStatus::REFUSEE,
        'rejection_reason' => 'Maturité insuffisante',
    ]);

    Mail::assertQueued(ApplicationDecisionMail::class, function ($mail) {
        return $mail->hasTo('founder2@test.com') && ! $mail->isWaitlist();
    });
});

test('coach assignment triggers emails to both startup and coach', function () {
    $coachUser = User::factory()->create(['email' => 'mentor@test.com']);
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $coachUser->id,
        'name' => 'Dr Mentor',
        'email' => 'mentor@test.com',
        'status' => 'ACTIF',
    ]);

    $founderUser = User::factory()->create(['email' => 'startup@test.com']);
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $founderUser->id,
        'first_name' => 'Awa',
        'last_name' => 'Diop',
        'email' => 'startup@test.com',
        'status' => 'ACTIVE',
    ]);
    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'AgriLogistics',
        'status' => ProjectStatus::ACTIF,
    ]);

    CoachAssignment::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'project_id' => $project->id,
        'status' => CoachAssignmentStatus::ACTIF,
        'assigned_at' => now(),
    ]);

    Mail::assertQueued(CoachAssignedStartupMail::class, function ($mail) {
        return $mail->hasTo('startup@test.com');
    });

    Mail::assertQueued(StartupAssignedToCoachMail::class, function ($mail) {
        return $mail->hasTo('mentor@test.com');
    });
});

test('scheduling a coaching session triggers email invitation', function () {
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'name' => 'Coach Expert',
        'email' => 'coach-session@test.com',
        'status' => 'ACTIF',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'first_name' => 'Paul',
        'last_name' => 'Sarr',
        'email' => 'founder-session@test.com',
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'SolarEnergy',
        'status' => ProjectStatus::ACTIF,
    ]);

    CoachingSession::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'type' => CoachingSessionType::INDIVIDUEL,
        'date' => now()->addDays(2),
        'duration' => 60,
        'theme' => 'Cadrage Business Model Canvas',
        'status' => CoachingSessionStatus::PLANIFIEE,
    ]);

    Mail::assertQueued(CoachingSessionScheduledMail::class, 2);
});

test('reviewing a deliverable triggers DeliverableStatusMail', function () {
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'first_name' => 'Sara',
        'last_name' => 'Toure',
        'email' => 'deliverable@test.com',
        'status' => 'ACTIVE',
    ]);

    $project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'CleanWater Africa',
        'status' => ProjectStatus::ACTIF,
    ]);

    $definition = DeliverableDefinition::create([
        'sae_id' => $this->sae->id,
        'name' => 'Pitch Deck Investisseurs',
    ]);

    $submission = DeliverableSubmission::create([
        'sae_id' => $this->sae->id,
        'definition_id' => $definition->id,
        'project_id' => $project->id,
        'entrepreneur_id' => $entrepreneur->id,
        'version' => 1,
        'file_name' => 'pitch_deck_v1.pdf',
        'status' => DeliverableSubmissionStatus::SOUMIS,
    ]);

    Mail::assertNothingQueued();

    $submission->update([
        'status' => DeliverableSubmissionStatus::VALIDE,
        'comment' => 'Excellent travail !',
    ]);

    Mail::assertQueued(DeliverableStatusMail::class, function ($mail) {
        return $mail->hasTo('deliverable@test.com') && $mail->isValidated();
    });
});

test('coach can submit a join request to an SAE and SAE receives notification', function () {
    $saeManager = User::factory()->create(['email' => 'manager@sae.com']);
    $saeManager->saes()->attach($this->sae->id, ['role' => 'RESPONSABLE', 'status' => 'ACTIVE']);

    $response = $this->post(route('public.coach.join.store'), [
        'sae_id' => $this->sae->id,
        'name' => 'Dr. Koffi Mensah',
        'email' => 'koffi.mensah@mentor.com',
        'phone' => '+228 90 11 22 33',
        'specialties' => 'Fintech, Gouvernance',
        'bio' => 'Plus de 15 ans d\'expérience dans l\'accompagnement de banques et fintechs.',
        'linkedin_url' => 'https://linkedin.com/in/koffi-mensah',
    ]);

    $response->assertRedirect(route('public.rejoindre'));

    $this->assertDatabaseHas('coach_join_requests', [
        'sae_id' => $this->sae->id,
        'email' => 'koffi.mensah@mentor.com',
        'status' => CoachJoinRequestStatus::PENDING->value,
    ]);

    Mail::assertQueued(CoachJoinRequestReceivedMail::class, function ($mail) {
        return $mail->hasTo('manager@sae.com');
    });
});

test('accepting a coach join request generates invitation and queues email with link', function () {
    $manager = User::factory()->create(['email' => 'manager2@sae.com']);

    $joinRequest = CoachJoinRequest::create([
        'sae_id' => $this->sae->id,
        'name' => 'Amina Diallo',
        'email' => 'amina.diallo@coach.com',
        'specialties' => ['Agritech', 'Supply Chain'],
        'bio' => 'Spécialiste agritech avec une forte expérience de terrain.',
        'status' => CoachJoinRequestStatus::PENDING,
    ]);

    $invitation = $joinRequest->accept($manager, maxCapacity: 4, isExternal: true);

    expect($joinRequest->fresh()->status)->toBe(CoachJoinRequestStatus::ACCEPTED);
    expect($invitation)->toBeInstanceOf(CoachInvitation::class);
    expect($invitation->token)->not->toBeEmpty();

    Mail::assertQueued(CoachJoinRequestAcceptedMail::class, function ($mail) use ($invitation) {
        return $mail->hasTo('amina.diallo@coach.com') && $mail->invitation->token === $invitation->token;
    });
});

test('founder welcome mail can be queued for new users', function () {
    $user = User::factory()->create(['email' => 'moussa.traore@founder.com']);
    $entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'first_name' => 'Moussa',
        'last_name' => 'Traore',
        'email' => 'moussa.traore@founder.com',
        'status' => 'ACTIVE',
    ]);

    Mail::to($user->email)->queue(new FounderWelcomeMail($user, $entrepreneur));

    Mail::assertQueued(FounderWelcomeMail::class, function ($mail) {
        return $mail->hasTo('moussa.traore@founder.com');
    });
});

test('coach welcome mail can be queued for new coaches', function () {
    $user = User::factory()->create(['email' => 'fatou.ndiaye@coach.com']);
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $user->id,
        'name' => 'Fatou Ndiaye',
        'email' => 'fatou.ndiaye@coach.com',
        'status' => 'ACTIF',
    ]);

    Mail::to($user->email)->queue(new CoachWelcomeMail($user, $coach, $this->sae));

    Mail::assertQueued(CoachWelcomeMail::class, function ($mail) {
        return $mail->hasTo('fatou.ndiaye@coach.com');
    });
});

test('sae manager welcome mail can be queued for managers', function () {
    $user = User::factory()->create(['email' => 'director@sae.com']);

    Mail::to($user->email)->queue(new SaeManagerWelcomeMail($user, $this->sae));

    Mail::assertQueued(SaeManagerWelcomeMail::class, function ($mail) {
        return $mail->hasTo('director@sae.com');
    });
});

test('direct coach invitation can be queued and rendered cleanly', function () {
    $invitation = CoachInvitation::create([
        'sae_id' => $this->sae->id,
        'name' => 'Paul Mensah',
        'email' => 'paul.mensah@coach.com',
        'specialties' => ['FinTech', 'Levée de fonds'],
        'max_capacity' => 5,
        'is_external' => true,
    ]);

    expect($invitation->token)->not->toBeEmpty();
    expect($invitation->getInvitationUrl())->toContain('/coach/invite/'.$invitation->token);

    Mail::to($invitation->email)->queue(new CoachDirectInvitationMail($invitation));

    Mail::assertQueued(CoachDirectInvitationMail::class, function ($mail) use ($invitation) {
        return $mail->hasTo('paul.mensah@coach.com') && $mail->invitation->id === $invitation->id;
    });

    $mailable = new CoachDirectInvitationMail($invitation);
    $html = $mailable->render();

    expect($html)->toContain('Paul Mensah');
    expect($html)->toContain($invitation->getInvitationUrl());
    expect($mailable->envelope()->subject)->toContain($this->sae->name);
});
