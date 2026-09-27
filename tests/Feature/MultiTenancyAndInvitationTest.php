<?php

use App\Models\Coach;
use App\Models\CoachInvitation;
use App\Models\Sae;
use App\Models\User;

test('a user can only create one SAE structure', function () {
    $user = User::factory()->create();

    expect($user->canCreateTenants())->toBeTrue();

    // Création de sa première SAE
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Mon Premier Hub',
        'slug' => 'mon-premier-hub',
    ]);

    $user->refresh();

    // La règle bloque toute création supplémentaire
    expect($user->canCreateTenants())->toBeFalse();
});

test('a coach can accept an invitation via token and join the SAE', function () {
    $sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Test Dakar',
        'slug' => 'sae-test-dakar',
    ]);

    $invitation = CoachInvitation::create([
        'sae_id' => $sae->id,
        'email' => 'nouveau.coach@test.sn',
        'name' => 'Nouveau Coach',
        'token' => 'token-test-invitation-12345',
        'max_capacity' => 8,
        'status' => 'PENDING',
        'expires_at' => now()->addDays(7),
    ]);

    $response = $this->get(route('coach.invite', $invitation->token));
    $response->assertOk();
    $response->assertSee('Nouveau Coach');
    $response->assertSee($sae->name);

    $postResponse = $this->post(route('coach.invite.register', $invitation->token), [
        'name' => 'Coach Validé',
        'password' => 'secret1234',
        'password_confirmation' => 'secret1234',
        'phone' => '+221 77 999 00 11',
    ]);

    $postResponse->assertRedirect();

    $this->assertAuthenticated();
    expect(Coach::where('email', 'nouveau.coach@test.sn')->exists())->toBeTrue();
    expect($invitation->fresh()->status)->toBe(\App\Enums\CoachInvitationStatus::ACCEPTED);
});

test('panels have functioning login and entry points', function () {
    $this->get('/sae/login')->assertOk();
    $this->get('/coach/login')->assertOk();
    $this->get('/portal/login')->assertOk();
    $this->get('/admin/login')->assertOk();
});

test('database seeder produces expected multi-tenant records and users', function () {
    $this->seed();

    expect(Sae::count())->toBeGreaterThanOrEqual(2);
    expect(User::where('email', 'admin@hubafrica.org')->exists())->toBeTrue();
    expect(User::where('email', 'fatou.diop@teranga.sn')->exists())->toBeTrue();
    expect(User::where('email', 'marie.sarr@coach.sn')->exists())->toBeTrue();
    expect(User::where('email', 'ousmane.fall@ecobat.sn')->exists())->toBeTrue();
});

test('authenticated users can access their respective panels and dashboards', function () {
    $this->seed();

    $admin = User::where('email', 'admin@hubafrica.org')->first();
    $saeUser = User::where('email', 'fatou.diop@teranga.sn')->first();
    $coachUser = User::where('email', 'marie.sarr@coach.sn')->first();
    $this->actingAs($admin)->get('/admin')->assertOk();
});

test('sae responsable can access sae tenant dashboard', function () {
    $this->seed();
    $saeUser = User::where('email', 'fatou.diop@teranga.sn')->first();
    $this->actingAs($saeUser)->get('/sae/teranga-africa')->assertOk();
});

test('coach can access coach tenant dashboard', function () {
    $this->seed();
    $coachUser = User::where('email', 'marie.sarr@coach.sn')->first();
    $this->actingAs($coachUser)->get('/coach/teranga-africa')->assertOk();
});

test('entrepreneur can access portal tenant dashboard', function () {
    $this->seed();
    $entrepreneurUser = User::where('email', 'ousmane.fall@ecobat.sn')->first();
    $this->actingAs($entrepreneurUser)->get('/portal/teranga-africa')->assertOk();
});

test('coach invitation notification has copy action and correct url', function () {
    $sae = Sae::first() ?? Sae::create(['name' => 'SAE Test', 'slug' => 'sae-test']);
    $invitation = CoachInvitation::create([
        'sae_id' => $sae->id,
        'email' => 'coach.copy@test.sn',
        'name' => 'Coach Copy Test',
        'token' => 'token-copy-test-999',
        'status' => 'PENDING',
    ]);

    expect($invitation->getInvitationUrl())->toContain('/coach/invite/token-copy-test-999');

    $notification = $invitation->makeInvitationNotification();
    expect($notification->getTitle())->toBe('Lien d\'invitation généré');
    expect((string) $notification->getBody())->toContain($invitation->getInvitationUrl());

    $actions = $notification->getActions();
    expect($actions)->not->toBeEmpty();

    $copyAction = collect($actions)->first(fn ($a) => $a->getName() === 'copy_invitation_url');
    expect($copyAction)->not->toBeNull();
    expect($copyAction->getLabel())->toBe('Copier le lien');
    expect($copyAction->getAlpineClickHandler())->toContain('navigator.clipboard.writeText');
    expect($copyAction->getAlpineClickHandler())->toContain($invitation->getInvitationUrl());
});

test('legacy register coach url with token redirects to official coach invite route', function () {
    $response = $this->get('/register/coach?token=demo-token-123');
    $response->assertRedirect(route('coach.invite', ['token' => 'demo-token-123']));
});



