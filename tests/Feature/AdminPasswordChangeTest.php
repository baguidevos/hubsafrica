<?php

use App\Filament\Admin\Resources\Users\Pages\EditUser;
use App\Filament\Admin\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Auth\Pages\EditProfile;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed();
    Filament::setCurrentPanel(Filament::getPanel('hubs-hq'));
});

test('admin can access profile page to change password', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    $response = $this->actingAs($admin)->get('/hubs-hq/profile');
    $response->assertOk();
});

test('admin can change password from profile page with valid credentials', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'currentPassword' => 'password',
            'password' => 'NewSecurePassword123!',
            'passwordConfirmation' => 'NewSecurePassword123!',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $admin->refresh();
    expect(Hash::check('NewSecurePassword123!', $admin->password))->toBeTrue();
});

test('admin cannot change password if current password is incorrect', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'currentPassword' => 'wrong-password',
            'password' => 'NewSecurePassword123!',
            'passwordConfirmation' => 'NewSecurePassword123!',
        ])
        ->call('save')
        ->assertHasFormErrors(['currentPassword']);

    $admin->refresh();
    expect(Hash::check('NewSecurePassword123!', $admin->password))->toBeFalse();
});

test('admin cannot change password if confirmation does not match', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(EditProfile::class)
        ->fillForm([
            'name' => $admin->name,
            'email' => $admin->email,
            'currentPassword' => 'password',
            'password' => 'NewSecurePassword123!',
            'passwordConfirmation' => 'DifferentPassword123!',
        ])
        ->call('save')
        ->assertHasFormErrors(['password']);
});

test('admin can change a user password via UsersTable action', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    $targetUser = User::factory()->create([
        'password' => 'old-password',
    ]);

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callTableAction('changePassword', $targetUser, [
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ])
        ->assertHasNoTableActionErrors();

    $targetUser->refresh();
    expect(Hash::check('BrandNewPassword123!', $targetUser->password))->toBeTrue();
});

test('admin can edit a user without re-entering password', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    $targetUser = User::factory()->create([
        'password' => 'initial-secret-password',
    ]);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $targetUser->getRouteKey()])
        ->fillForm([
            'name' => 'Updated User Name',
            'password' => null,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $targetUser->refresh();
    expect($targetUser->name)->toBe('Updated User Name')
        ->and(Hash::check('initial-secret-password', $targetUser->password))->toBeTrue();
});

test('admin can update a user password via EditUser page', function () {
    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    $targetUser = User::factory()->create([
        'password' => 'initial-secret-password',
    ]);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $targetUser->getRouteKey()])
        ->fillForm([
            'password' => 'UpdatedViaForm123!',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $targetUser->refresh();
    expect(Hash::check('UpdatedViaForm123!', $targetUser->password))->toBeTrue();
});
