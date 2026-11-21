<?php

use App\Enums\EntrepreneurStatus;
use App\Filament\Entrepreneur\Resources\Projects\Pages\CreateProject as EntrepreneurCreateProject;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Schema;
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

test('projects table stores target and market as text columns', function () {
    expect(Schema::getColumnType('projects', 'target'))->toBe('text');
    expect(Schema::getColumnType('projects', 'market'))->toBe('text');
});

test('entrepreneur can create a project with long target and market descriptions', function () {
    $longTarget = trim(str_repeat('B2C, B2B et B2G à Daloa et dans le Haut-Sassandra. ', 8));
    $longMarket = trim(str_repeat('Marché privé de Daloa et extension vers Abidjan avec demande quotidienne forte. ', 6));

    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(EntrepreneurCreateProject::class)
        ->fillForm([
            'name' => 'Production et vente de manioc',
            'sector' => 'Agriculture',
            'territory' => 'Daloa',
            'team_size' => 4,
            'target' => $longTarget,
            'market' => $longMarket,
            'value_proposition' => 'Du manioc frais, de qualité et disponible toute l\'année.',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified()
        ->assertRedirect();

    $project = Project::where('entrepreneur_id', $this->entrepreneur->id)->first();

    expect($project)->not->toBeNull();
    expect($project->target)->toBe(trim($longTarget));
    expect($project->market)->toBe(trim($longMarket));
});

test('entrepreneur project form rejects name and territory exceeding 255 characters', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(EntrepreneurCreateProject::class)
        ->fillForm([
            'name' => str_repeat('a', 256),
            'sector' => 'Agriculture',
            'territory' => str_repeat('b', 256),
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'max',
            'territory' => 'max',
        ])
        ->assertNotNotified();
});
