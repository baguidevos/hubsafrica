<?php

use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Filament\Entrepreneur\Pages\ProfilePage;
use App\Models\Entrepreneur;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Impact Hub Abidjan',
        'slug' => 'sae-impact-hub-abidjan',
    ]);

    $this->entrepreneurUser = User::factory()->create();
    $this->entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $this->entrepreneurUser->id,
        'first_name' => 'Ahou',
        'last_name' => 'Kouassi',
        'email' => 'ahou.kouassi@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
});

test('entrepreneur can save profile when gender is left empty', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(ProfilePage::class)
        ->set('firstName', 'Ahou')
        ->set('lastName', 'Kouassi')
        ->set('email', 'ahou.kouassi@example.com')
        ->set('gender', '')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified();

    expect($this->entrepreneur->fresh()->gender)->toBeNull();
});

test('entrepreneur can save profile with a valid gender enum value', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(ProfilePage::class)
        ->set('firstName', 'Ahou')
        ->set('lastName', 'Kouassi')
        ->set('email', 'ahou.kouassi@example.com')
        ->set('gender', 'F')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified();

    expect($this->entrepreneur->fresh()->gender)->toBe(Gender::F);
});

test('entrepreneur profile rejects an invalid gender value', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(ProfilePage::class)
        ->set('firstName', 'Ahou')
        ->set('lastName', 'Kouassi')
        ->set('email', 'ahou.kouassi@example.com')
        ->set('gender', 'INVALID')
        ->call('save')
        ->assertHasErrors(['gender' => 'in']);
});

test('entrepreneur profile displays and saves default education level and professional status options', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(ProfilePage::class)
        ->assertSee('Licence / Bac+3')
        ->assertSee('Master / Ingénieur / Bac+5')
        ->assertSee('Indépendant / Auto-entrepreneur')
        ->set('firstName', 'Ahou')
        ->set('lastName', 'Kouassi')
        ->set('email', 'ahou.kouassi@example.com')
        ->set('educationLevel', 'Licence')
        ->set('professionalStatus', 'Indépendant')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotified();

    $fresh = $this->entrepreneur->fresh();
    expect($fresh->education_level)->toBe('Licence')
        ->and($fresh->professional_status)->toBe('Indépendant');
});
