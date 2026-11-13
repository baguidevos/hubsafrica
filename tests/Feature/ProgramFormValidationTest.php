<?php

use App\Enums\ProgramStatus;
use App\Filament\Sae\Resources\Programs\Pages\CreateProgram;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Impact Hub Cotonou',
        'slug' => 'sae-impact-hub-cotonou',
    ]);

    $adminUser = User::factory()->create();
    $this->actingAs($adminUser);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($this->sae);
});

test('create program succeeds with valid data and coherent dates and thresholds', function () {
    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => 'Programme FinTech 2026',
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
            'is_active' => true,
            'capacity' => 25,
            'registration_start' => '2026-05-01',
            'registration_end' => '2026-05-31',
            'start_date' => '2026-06-15',
            'end_date' => '2026-12-15',
            'admission_threshold' => 75,
            'waitlist_threshold' => 50,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Program::where('name', 'Programme FinTech 2026')->exists())->toBeTrue();
});

test('create program validates required, length, and uniqueness rules on name and capacity', function () {
    Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme Existant',
        'status' => ProgramStatus::BROUILLON,
    ]);

    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => '',
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);

    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => 'AB',
            'capacity' => 0,
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'min',
            'capacity' => 'min',
        ]);

    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => 'Programme Existant',
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique']);
});

test('create program rejects incoherent registration and program dates', function () {
    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => 'Programme Dates Invalides',
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
            'registration_start' => '2026-06-01',
            'registration_end' => '2026-05-15',
            'start_date' => '2026-09-01',
            'end_date' => '2026-08-01',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'registration_end' => 'after_or_equal',
            'end_date' => 'after_or_equal',
        ]);
});

test('create program rejects waitlist threshold greater than or equal to admission threshold', function () {
    Livewire::test(CreateProgram::class)
        ->fillForm([
            'name' => 'Programme Seuils Invalides',
            'status' => ProgramStatus::OUVERT_CANDIDATURES->value,
            'admission_threshold' => 70,
            'waitlist_threshold' => 70,
        ])
        ->call('create')
        ->assertHasFormErrors(['waitlist_threshold']);
});
