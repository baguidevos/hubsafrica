<?php

use App\Filament\Admin\Widgets\PlatformMonthlyGrowthChartWidget;
use App\Filament\Admin\Widgets\PlatformSectorDistributionChartWidget;
use App\Filament\Admin\Widgets\PlatformTopSaesWidget;
use App\Filament\Admin\Widgets\PlatformTractionKpiBannerWidget;
use App\Filament\Admin\Widgets\PlatformTractionOverviewWidget;
use App\Models\User;
use Livewire\Livewire;

test('admin dashboard renders platform traction metrics widgets correctly', function () {
    $this->seed();

    $admin = User::where('email', 'admin@hubsafrica.org')->first();
    expect($admin)->not->toBeNull();

    // 1. Dashboard HTTP GET
    $response = $this->actingAs($admin)->get('/admin');
    $response->assertOk();
    $response->assertSee('Mesures de Traction', false);
    $response->assertSee('Structures (SAE) raccordées', false);
    $response->assertSee('Entrepreneurs accompagnés', false);
    $response->assertSee('ODD 8 : Travail Décent', false);
    $response->assertSee('Réseau Panafricain', false);

    // 2. Test Livewire widget rendering
    Livewire::actingAs($admin)
        ->test(PlatformTractionOverviewWidget::class)
        ->assertSuccessful();

    Livewire::actingAs($admin)
        ->test(PlatformTractionKpiBannerWidget::class)
        ->assertSuccessful()
        ->assertSee('ODD 8 : Travail Décent')
        ->assertSee('Féminisation');

    Livewire::actingAs($admin)
        ->test(PlatformMonthlyGrowthChartWidget::class)
        ->assertSuccessful();

    Livewire::actingAs($admin)
        ->test(PlatformSectorDistributionChartWidget::class)
        ->assertSuccessful();

    Livewire::actingAs($admin)
        ->test(PlatformTopSaesWidget::class)
        ->assertSuccessful()
        ->assertSee('Gérer toutes les SAE');
});
