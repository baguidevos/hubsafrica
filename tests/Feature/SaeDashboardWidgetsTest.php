<?php

use App\Enums\ApplicationStatus;
use App\Enums\CoachStatus;
use App\Enums\ProgramStatus;
use App\Filament\Sae\Widgets\ApplicationsChartWidget;
use App\Filament\Sae\Widgets\CoachWorkloadWidget;
use App\Filament\Sae\Widgets\PerformanceRatesWidget;
use App\Filament\Sae\Widgets\PortfolioSectorChartWidget;
use App\Filament\Sae\Widgets\SaeStatsOverviewWidget;
use App\Models\Application;
use App\Models\Coach;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('sae dashboard widgets do not fabricate fallback data when empty', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Vide',
        'slug' => 'sae-vide-'.uniqid(),
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);

    Livewire::test(SaeStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('Entrepreneurs accompagnés')
        ->assertSee('Accordés, décaissés et investis');

    $rates = app(PerformanceRatesWidget::class)->getFormulesData();
    $byKey = collect($rates)->keyBy('key');

    expect($byKey['validation']['val'])->toBe('0 %')
        ->and($byKey['remplissage']['val'])->toBe('0 %')
        ->and($byKey['remplissage']['den'])->toBe('Capacité non définie (0)')
        ->and($byKey['chargeCoach']['tone'])->toBe('rose');

    $sectorChart = new class extends PortfolioSectorChartWidget
    {
        public function exposedData(): array
        {
            return $this->getData();
        }
    };
    expect($sectorChart->exposedData()['labels'])->toBeEmpty();

    $appChart = new class extends ApplicationsChartWidget
    {
        public function exposedData(): array
        {
            return $this->getData();
        }
    };
    expect(array_sum($appChart->exposedData()['datasets'][0]['data']))->toBe(0);
});

test('sae dashboard widgets count both active status variants and exclude draft applications', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Active',
        'slug' => 'sae-active-'.uniqid(),
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);

    $programLance = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Lancé',
        'status' => ProgramStatus::LANCE,
    ]);

    Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Actif',
        'status' => ProgramStatus::ACTIF,
    ]);

    Coach::create([
        'sae_id' => $sae->id,
        'name' => 'Coach FR',
        'email' => 'coach-fr@example.com',
        'status' => CoachStatus::ACTIF,
    ]);

    Coach::create([
        'sae_id' => $sae->id,
        'name' => 'Coach EN',
        'email' => 'coach-en@example.com',
        'status' => CoachStatus::ACTIVE,
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Awa',
        'last_name' => 'Diallo',
        'email' => 'awa@example.com',
        'status' => 'ACTIVE',
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programLance->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::BROUILLON,
    ]);

    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programLance->id,
        'entrepreneur_id' => $entrepreneur->id,
        'status' => ApplicationStatus::SOUMISE,
    ]);

    $stats = app(SaeStatsOverviewWidget::class);
    Livewire::test(SaeStatsOverviewWidget::class)
        ->assertSuccessful()
        ->assertSee('2') // 2 programmes actifs, 2 coachs mobilisés
        ->assertSee('1'); // 1 candidature reçue (brouillon exclu)

    $workloads = app(CoachWorkloadWidget::class)->getCoachWorkloads();
    expect($workloads)->toHaveCount(2);

    $rates = collect(app(PerformanceRatesWidget::class)->getFormulesData())->keyBy('key');
    expect($rates['chargeCoach']['tone'])->toBe('emerald')
        ->and($rates['chargeCoach']['den'])->toBe('Coachs actifs (2)');
});
