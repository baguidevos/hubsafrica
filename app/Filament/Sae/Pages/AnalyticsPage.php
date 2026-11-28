<?php

namespace App\Filament\Sae\Pages;

use App\Models\Cohort;
use App\Models\Program;
use App\Models\Sae;
use App\Services\ReportExportService;
use App\Services\SaeCountryBreakdownService;
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

    public ?string $selectedProgramId = 'all';

    public ?string $selectedCohortId = 'all';

    public function getActiveTenant(): ?Sae
    {
        return Filament::getTenant();
    }

    public function getProgramsProperty()
    {
        $tenant = $this->getActiveTenant();

        return Program::query()
            ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->id))
            ->get();
    }

    public function getCohortsProperty()
    {
        $tenant = $this->getActiveTenant();

        return Cohort::query()
            ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->id))
            ->when($this->selectedProgramId && $this->selectedProgramId !== 'all', fn ($q) => $q->where('program_id', $this->selectedProgramId))
            ->get();
    }

    public function getAnalyticsData(): array
    {
        $tenant = $this->getActiveTenant();
        $service = app(ReportExportService::class);
        $filters = [
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ];

        if ($tenant) {
            return $service->getAnalyticsData($tenant, $filters);
        }

        return $service->getAnalyticsData(new Sae(['name' => 'Hubs Africa SAE', 'slug' => 'sae']), $filters);
    }

    public function getPrintUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.print', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function getDownloadUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.download', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function getCsvUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.analytics.csv', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $tenant = $this->getActiveTenant() ?? new Sae(['name' => 'Hubs Africa', 'slug' => 'hubsafrica']);
        $service = app(ReportExportService::class);
        $data = $this->getAnalyticsData();

        return $service->exportAnalyticsCsv($tenant, $data, [
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function getCountryBreakdown(): array
    {
        $tenant = $this->getActiveTenant();

        if (! $tenant) {
            return [
                'summary' => [
                    'total_accounts' => 0,
                    'accounts_with_application' => 0,
                    'accounts_with_project' => 0,
                    'accounts_without_project' => 0,
                    'total_applications' => 0,
                    'total_calls' => 0,
                ],
                'global_by_country' => [],
                'by_call_and_country' => [],
            ];
        }

        $programId = ($this->selectedProgramId && $this->selectedProgramId !== 'all')
            ? (int) $this->selectedProgramId
            : null;
        $cohortId = ($this->selectedCohortId && $this->selectedCohortId !== 'all')
            ? (int) $this->selectedCohortId
            : null;

        return app(SaeCountryBreakdownService::class)->buildForSae($tenant, $programId, $cohortId);
    }

    public function exportCountryBreakdownCsv(): StreamedResponse
    {
        $tenant = $this->getActiveTenant() ?? new Sae(['name' => 'Hubs Africa', 'slug' => 'hubsafrica']);
        $breakdown = $this->getCountryBreakdown();

        return app(SaeCountryBreakdownService::class)->streamCsv($tenant, $breakdown);
    }
}
