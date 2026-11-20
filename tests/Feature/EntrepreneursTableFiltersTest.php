<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('sae entrepreneurs table filters by program and by country', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Filtres',
        'slug' => 'sae-filtres-'.uniqid(),
    ]);

    $programAlpha = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Alpha',
        'slug' => 'programme-alpha-'.uniqid(),
    ]);

    $programBeta = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Beta',
        'slug' => 'programme-beta-'.uniqid(),
    ]);

    $cohortAlpha = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $programAlpha->id,
        'name' => 'Cohorte Alpha 1',
        'status' => CohortStatus::EN_COURS,
    ]);

    $entrepreneurEnrolled = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Aminata',
        'last_name' => 'Sow',
        'email' => 'aminata@example.com',
        'country' => 'Sénégal',
    ]);

    Enrollment::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohortAlpha->id,
        'entrepreneur_id' => $entrepreneurEnrolled->id,
        'status' => 'ACTIF',
        'enrolled_at' => now(),
    ]);

    $entrepreneurApplicant = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => 'koffi@example.com',
        'country' => 'Togo',
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programAlpha->id,
        'entrepreneur_id' => $entrepreneurApplicant->id,
        'status' => ApplicationStatus::EN_ATTENTE,
        'submitted_at' => now(),
    ]);

    $entrepreneurBeta = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Bamba',
        'email' => 'fatou@example.com',
        'country' => 'Côte d\'Ivoire',
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programBeta->id,
        'entrepreneur_id' => $entrepreneurBeta->id,
        'status' => ApplicationStatus::EN_ATTENTE,
        'submitted_at' => now(),
    ]);

    $this->actingAs($owner);
    Filament::setCurrentPanel('sae');
    Filament::setTenant($sae);

    Livewire::test(ListEntrepreneurs::class)
        ->loadTable()
        ->filterTable('program_id', $programAlpha->id)
        ->assertCanSeeTableRecords([$entrepreneurEnrolled, $entrepreneurApplicant])
        ->assertCanNotSeeTableRecords([$entrepreneurBeta])
        ->resetTableFilters()
        ->filterTable('country', 'Togo')
        ->assertCanSeeTableRecords([$entrepreneurApplicant])
        ->assertCanNotSeeTableRecords([$entrepreneurEnrolled, $entrepreneurBeta]);
});

test('sae entrepreneurs table sorts by full_name using first_name and last_name', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Tri',
        'slug' => 'sae-tri-'.uniqid(),
    ]);

    $zara = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Zara',
        'last_name' => 'Diallo',
        'email' => 'zara@example.com',
    ]);

    $amina = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Amina',
        'last_name' => 'Kone',
        'email' => 'amina@example.com',
    ]);

    $this->actingAs($owner);
    Filament::setCurrentPanel('sae');
    Filament::setTenant($sae);

    Livewire::test(ListEntrepreneurs::class)
        ->loadTable()
        ->sortTable('full_name', 'asc')
        ->assertCanSeeTableRecords([$amina, $zara], inOrder: true)
        ->sortTable('full_name', 'desc')
        ->assertCanSeeTableRecords([$zara, $amina], inOrder: true);
});
