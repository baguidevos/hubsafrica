<?php

use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Filament\Coach\Resources\Projects\Pages\ViewProject as CoachViewProject;
use App\Filament\Entrepreneur\Resources\Projects\Pages\ViewProject as EntrepreneurViewProject;
use App\Filament\Sae\Resources\Projects\Pages\ViewProject as SaeViewProject;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Impact Hub Cotonou',
        'slug' => 'sae-impact-hub-cotonou',
    ]);

    $this->entrepreneurUser = User::factory()->create();
    $this->entrepreneur = Entrepreneur::create([
        'sae_id' => $this->sae->id,
        'user_id' => $this->entrepreneurUser->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => 'koffi.mensah@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $this->project = Project::create([
        'sae_id' => $this->sae->id,
        'entrepreneur_id' => $this->entrepreneur->id,
        'name' => 'SolarAgri Benin',
        'pitch' => 'Irrigation solaire intelligente pour petits producteurs.',
        'description' => 'Plateforme IoT et pompe solaire autonome.',
        'problem' => 'Accès limité à l\'irrigation hors réseau.',
        'solution' => 'Kit solaire connecté à faible coût.',
        'sector' => 'AgriTech',
        'territory' => 'Cotonou, Bénin',
        'team_size' => 4,
        'status' => ProjectStatus::ACTIF,
    ]);
});

test('sae panel view project page renders custom project detail view', function () {
    $saeUser = User::factory()->create();
    $this->actingAs($saeUser);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);

    Livewire::test(SaeViewProject::class, ['record' => $this->project->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('SolarAgri Benin')
        ->assertSee('Irrigation solaire intelligente pour petits producteurs.')
        ->assertSee('Koffi Mensah')
        ->assertSee('AgriTech');
});

test('coach panel view project page renders custom project detail view for assigned coach', function () {
    $coachUser = User::factory()->create();
    $coach = Coach::create([
        'sae_id' => $this->sae->id,
        'user_id' => $coachUser->id,
        'name' => 'Awa Diallo',
        'email' => 'awa.diallo@example.com',
    ]);

    CoachAssignment::create([
        'sae_id' => $this->sae->id,
        'coach_id' => $coach->id,
        'project_id' => $this->project->id,
        'status' => 'ACTIF',
    ]);

    $this->actingAs($coachUser);
    Filament::setCurrentPanel(Filament::getPanel('coach'));
    Filament::setTenant($this->sae);

    Livewire::test(CoachViewProject::class, ['record' => $this->project->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('SolarAgri Benin')
        ->assertSee('Awa Diallo')
        ->assertSee('Koffi Mensah');
});

test('entrepreneur panel view project page renders custom project detail view', function () {
    $this->actingAs($this->entrepreneurUser);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($this->sae);

    Livewire::test(EntrepreneurViewProject::class, ['record' => $this->project->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('SolarAgri Benin')
        ->assertSee('Plateforme IoT et pompe solaire autonome.')
        ->assertSee('Cotonou, Bénin');
});
