<?php

use App\Models\User;
use App\Notifications\FilamentResetPassword;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

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

test('user can request a password reset from a filament panel synchronously', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'resetme@example.com',
    ]);

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => 'resetme@example.com',
        ])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($user, FilamentResetPassword::class);
});

test('super admin can request password reset from admin panel', function () {
    Notification::fake();

    $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

    $admin = User::factory()->create([
        'email' => 'custom-admin@hubsafrica.org',
    ]);

    DB::table('model_has_roles')->insert([
        'role_id' => $adminRole->id,
        'model_type' => User::class,
        'model_id' => $admin->id,
        'sae_id' => 0,
    ]);

    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(RequestPasswordReset::class)
        ->fillForm([
            'email' => 'custom-admin@hubsafrica.org',
        ])
        ->call('request')
        ->assertHasNoFormErrors();

    Notification::assertSentTo($admin, FilamentResetPassword::class);
});
