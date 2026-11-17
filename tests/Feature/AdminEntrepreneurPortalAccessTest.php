<?php

use App\Filament\Admin\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Models\Entrepreneur;
use App\Models\User;
use Livewire\Livewire;

test('super admin can view entrepreneurs list in admin panel with filters and tabs', function () {
    $this->seed();

    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    // 1. HTTP GET /hubs-hq/entrepreneurs
    $response = $this->actingAs($admin)->get('/hubs-hq/entrepreneurs');
    $response->assertOk();
    $response->assertSee('Portail Entrepreneurs');

    // 2. Test Livewire component
    Livewire::actingAs($admin)
        ->test(ListEntrepreneurs::class)
        ->assertSuccessful()
        ->assertSee('Projets remplis')
        ->assertSee('Structure SAE');
});

test('admin can filter entrepreneurs by activity status and filled projects', function () {
    $this->seed();

    $admin = User::where('email', 'admin@hubsafrica.org')->first();

    // Filter by activity: active
    Livewire::actingAs($admin)
        ->test(ListEntrepreneurs::class)
        ->filterTable('activity', 'active')
        ->assertSuccessful();

    // Filter by activity: inactive
    Livewire::actingAs($admin)
        ->test(ListEntrepreneurs::class)
        ->filterTable('activity', 'inactive')
        ->assertSuccessful();

    // Filter by projects: with_projects
    Livewire::actingAs($admin)
        ->test(ListEntrepreneurs::class)
        ->filterTable('has_projects', 'with_projects')
        ->assertSuccessful();

    // Filter by projects: without_projects
    Livewire::actingAs($admin)
        ->test(ListEntrepreneurs::class)
        ->filterTable('has_projects', 'without_projects')
        ->assertSuccessful();
});

test('super admin can impersonate entrepreneur to access their portal and then stop impersonating', function () {
    $this->seed();

    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    $entrepreneur = Entrepreneur::whereNotNull('user_id')->first();
    expect($entrepreneur)->not->toBeNull();

    // 1. Admin accesses impersonation route and follows redirect to portal
    $response = $this->actingAs($admin)
        ->followingRedirects()
        ->get(route('admin.impersonate.entrepreneur', $entrepreneur));

    $response->assertOk();
    $response->assertSee('Mode Supervision Super Admin');
    $response->assertSee('Quitter l\'aperçu', false);

    // Authenticated user should be the entrepreneur and session tracks admin
    expect(auth()->id())->toBe($entrepreneur->user_id);
    expect(session('impersonator_admin_id'))->toBe($admin->id);

    // 2. Stop impersonation
    $stopResponse = $this->followingRedirects()
        ->get(route('admin.impersonate.stop'));

    $stopResponse->assertOk();

    // Now authenticated user is the admin again
    expect(auth()->id())->toBe($admin->id);
    expect(session()->has('impersonator_admin_id'))->toBeFalse();
});

test('non-super admin cannot impersonate an entrepreneur', function () {
    $this->seed();

    $regularUser = User::where('email', '!=', 'admin@hubsafrica.org')->first();
    $entrepreneur = Entrepreneur::whereNotNull('user_id')->first();

    $response = $this->actingAs($regularUser)->get(route('admin.impersonate.entrepreneur', $entrepreneur));
    $response->assertForbidden();
});
