<?php

namespace App\Filament\Sae\Pages;

use App\Models\Sae;
use App\Services\ReportExportService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsPage extends Page
{
    protected string $view = 'filament.sae.pages.analytics-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Centre d\'analyse';

    protected static ?string $title = 'Centre d\'analyse & Performance du portefeuille';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 3;

    public function getActiveTenant(): ?Sae
    {
        return Filament::getTenant();
    }

    public function getAnalyticsData(): array
    {
        $tenant = $this->getActiveTenant();
        $service = app(ReportExportService::class);

        if ($tenant) {
            return $service->getAnalyticsData($tenant);
        }

        return $service->getAnalyticsData(new Sae(['name' => 'Hubs Africa SAE', 'slug' => 'sae']));
    }

    public function getPrintUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.print', [
            'tenant' => $tenant?->slug ?? 'default',
        ]);
    }

    public function getDownloadUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.download', [
            'tenant' => $tenant?->slug ?? 'default',
        ]);
    }

    public function getCsvUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.csv', [
            'tenant' => $tenant?->slug ?? 'default',
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $tenant = $this->getActiveTenant() ?? new Sae(['name' => 'Hubs Africa', 'slug' => 'hubsafrica']);
        $service = app(ReportExportService::class);
        $data = $this->getAnalyticsData();

        return $service->exportAnalyticsCsv($tenant, $data);
    }
}
