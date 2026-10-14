<?php

use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('password reset request routes are accessible across panels', function () {
    $this->get(route('filament.admin.auth.password-reset.request'))->assertSuccessful();
    $this->get(route('filament.sae.auth.password-reset.request'))->assertSuccessful();
    $this->get(route('filament.entrepreneur.auth.password-reset.request'))->assertSuccessful();
    $this->get(route('filament.coach.auth.password-reset.request'))->assertSuccessful();
});

test('cohort join page contains link to entrepreneur password reset', function () {
    $response = $this->get('/rejoindre');
    $response->assertSuccessful();
});

test('user can request a password reset from a filament panel', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'resetme@example.com',
    ]);

    // Livewire request to filament.entrepreneur.auth.password-reset.request
    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => 'resetme@example.com',
        ])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, Filament\Auth\Notifications\ResetPassword::class);
});
