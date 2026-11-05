<?php

use App\Enums\CoachInvitationStatus;
use App\Enums\EntrepreneurInvitationStatus;
use App\Models\CoachInvitation;
use App\Models\EntrepreneurInvitation;
use App\Models\Sae;
use App\Models\User;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Hub Innovation',
        'slug' => 'sae-hub-innovation',
    ]);
});

test('coach invitation generates a compact neutral join url', function () {
    $invitation = CoachInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'coach.neutral@test.sn',
        'name' => 'Coach Expert',
    ]);

    expect(strlen($invitation->token))->toBe(20)
        ->and($invitation->getInvitationUrl())->toBe(route('invitation.join', ['token' => $invitation->token]))
        ->and($invitation->getInvitationUrl())->toContain('/join/')
        ->and($invitation->getInvitationUrl())->not->toContain('/coach/');
});

test('entrepreneur invitation generates a compact neutral join url', function () {
    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'entrepreneur.neutral@test.sn',
        'first_name' => 'Amadou',
        'last_name' => 'Diallo',
    ]);

    expect(strlen($invitation->token))->toBe(20)
        ->and($invitation->getInvitationUrl())->toBe(route('invitation.join', ['token' => $invitation->token]))
        ->and($invitation->getInvitationUrl())->toContain('/join/')
        ->and($invitation->getInvitationUrl())->not->toContain('/entrepreneur/');
});

test('unified join url renders coach onboarding page for coach token', function () {
    $invitation = CoachInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'mentor.display@test.sn',
        'name' => 'Mentor Alioune',
    ]);

    $response = $this->get(route('invitation.join', ['token' => $invitation->token]));

    $response->assertOk();
    $response->assertSee('mentor.display@test.sn');
    $response->assertSee($this->sae->name);
});

test('unified join url renders entrepreneur onboarding page for entrepreneur token', function () {
    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'porteur.display@test.sn',
        'first_name' => 'Khadija',
        'last_name' => 'Sy',
    ]);

    $response = $this->get(route('invitation.join', ['token' => $invitation->token]));

    $response->assertOk();
    $response->assertSee('porteur.display@test.sn');
    $response->assertSee('Khadija');
    $response->assertSee($this->sae->name);
});

test('unified join url displays friendly error when token is invalid or expired', function () {
    $response = $this->get(route('invitation.join', ['token' => 'invalid-token-12345']));

    $response->assertOk();
    $response->assertSee('Lien d\'invitation', false);

    $expiredInvitation = CoachInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'expired.coach@test.sn',
        'status' => CoachInvitationStatus::EXPIRED,
        'expires_at' => now()->subDay(),
    ]);

    $expiredResponse = $this->get(route('invitation.join', ['token' => $expiredInvitation->token]));
    $expiredResponse->assertOk();
    $expiredResponse->assertSee('Lien d\'invitation', false);
});

test('coach can register via unified join route', function () {
    $invitation = CoachInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'coach.unified.reg@test.sn',
        'name' => 'Coach Enregistre',
    ]);

    $response = $this->post(route('invitation.register', ['token' => $invitation->token]), [
        'name' => 'Coach Final Name',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'phone' => '+221770000000',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $invitation->refresh();
    expect($invitation->status)->toBe(CoachInvitationStatus::ACCEPTED);

    $user = User::where('email', 'coach.unified.reg@test.sn')->first();
    expect($user)->not->toBeNull()
        ->and($user->isCoach())->toBeTrue();
});

test('entrepreneur can register via unified join route', function () {
    $invitation = EntrepreneurInvitation::create([
        'sae_id' => $this->sae->id,
        'email' => 'entrepreneur.unified.reg@test.sn',
        'first_name' => 'Ousmane',
        'last_name' => 'Ba',
    ]);

    $response = $this->post(route('invitation.register', ['token' => $invitation->token]), [
        'first_name' => 'Ousmane',
        'last_name' => 'Ba',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'phone' => '+221780000000',
        'project_name' => 'AgriTech Sahel',
    ]);

    $response->assertRedirect();
    $this->assertAuthenticated();

    $invitation->refresh();
    expect($invitation->status)->toBe(EntrepreneurInvitationStatus::ACCEPTED);

    $user = User::where('email', 'entrepreneur.unified.reg@test.sn')->first();
    expect($user)->not->toBeNull()
        ->and($user->isEntrepreneur())->toBeTrue();
});
