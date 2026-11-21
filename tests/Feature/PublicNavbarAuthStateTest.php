<?php

use App\Enums\EntrepreneurStatus;
use App\Models\Entrepreneur;
use App\Models\Sae;
use App\Models\User;

test('public navbar displays guest actions when no session is active', function () {
    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Espaces Dédiés')
        ->assertSee('Rejoindre')
        ->assertDontSee('Se déconnecter');
});

test('public navbar displays active entrepreneur session and portal shortcut when authenticated', function () {
    $sae = Sae::create([
        'name' => 'SAE Navbar Test',
        'slug' => 'sae-navbar-'.uniqid(),
    ]);

    $user = User::factory()->create([
        'name' => 'Aminata Diallo',
        'email' => 'aminata.diallo@example.com',
    ]);

    Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $user->id,
        'first_name' => 'Aminata',
        'last_name' => 'Diallo',
        'email' => $user->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Aminata Diallo')
        ->assertSee('Mon espace')
        ->assertSee('/portal')
        ->assertSee('Se déconnecter')
        ->assertDontSee('Espaces Dédiés');
});

test('public navbar displays sae portal shortcut for an authenticated sae manager', function () {
    $manager = User::factory()->create([
        'name' => 'Directeur SAE',
    ]);

    Sae::create([
        'owner_id' => $manager->id,
        'name' => 'SAE Owner Test',
        'slug' => 'sae-owner-'.uniqid(),
    ]);

    $this->actingAs($manager)
        ->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Directeur SAE')
        ->assertSee('Mon espace SAE')
        ->assertSee('/sae')
        ->assertSee('Se déconnecter')
        ->assertDontSee('Espaces Dédiés');
});
