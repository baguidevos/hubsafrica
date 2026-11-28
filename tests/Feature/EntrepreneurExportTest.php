<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\EntrepreneurExportService;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('entrepreneur export includes oui/non flags for project, application, and enrollment', function () {
    $sae = Sae::create([
        'name' => 'SAE Export Porteurs',
        'slug' => 'sae-export-porteurs',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme AgriPulse',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 30,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte 1',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 30,
    ]);

    // 1. Entrepreneur with project + application + enrollment
    $entComplete = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Aminata',
        'last_name' => 'Touré',
        'email' => 'aminata.toure@example.com',
        'country' => 'Sénégal',
        'status' => 'ACTIVE',
    ]);
    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entComplete->id,
        'name' => 'AgriSolar',
        'status' => ProjectStatus::ACTIF,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entComplete->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);
    Enrollment::create([
        'sae_id' => $sae->id,
        'cohort_id' => $cohort->id,
        'entrepreneur_id' => $entComplete->id,
        'project_id' => $project->id,
        'status' => 'ENROLLED',
    ]);

    // 2. Entrepreneur without project, without application, not enrolled
    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => 'koffi.mensah@example.com',
        'country' => 'Togo',
        'status' => 'CANDIDAT',
    ]);

    $response = app(EntrepreneurExportService::class)->streamEntrepreneursCsv($sae->id, $sae->slug);

    ob_start();
    $response->sendContent();
    $csvContent = (string) ob_get_clean();

    expect($csvContent)
        ->toContain('A un projet')
        ->toContain('A une candidature')
        ->toContain('Enrôlé')
        ->toContain('aminata.toure@example.com')
        ->toContain(';Oui;1;Oui;1;Oui;"Programme AgriPulse — Cohorte 1"')
        ->toContain('koffi.mensah@example.com')
        ->toContain(';Non;0;Non;0;Non;—');
});

test('sae entrepreneurs list header action exports csv', function () {
    $sae = Sae::create([
        'name' => 'SAE Liste Export',
        'slug' => 'sae-liste-export',
    ]);

    $user = User::factory()->create();
    $user->saes()->attach($sae->id, ['role' => 'admin']);

    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Fatou',
        'last_name' => 'Sall',
        'email' => 'fatou.sall@example.com',
        'country' => 'Sénégal',
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    Livewire::test(ListEntrepreneurs::class)
        ->callAction('export_entrepreneurs_csv')
        ->assertFileDownloaded();
});
