<?php

namespace App\Filament\Sae\Pages;

use App\Models\Cohort;
use App\Models\Program;
use App\Models\Sae;
use App\Services\ReportExportService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsPage extends Page
{
    protected string $view = 'filament.sae.pages.reports-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Rapports';

    protected static ?string $title = 'Rapports & Indicateurs d\'impact';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?int $navigationSort = 2;

    public ?string $selectedProgramId = 'all';

    public ?string $selectedCohortId = 'all';

    public function getActiveTenant(): ?Sae
    {
        return Filament::getTenant();
    }

    public function getProgramsProperty()
    {
        $tenant = $this->getActiveTenant();
        $query = Program::query();

        if ($tenant) {
            $query->where('sae_id', $tenant->id);
        }

        return $query->get();
    }

    public function getCohortsProperty()
    {
        $tenant = $this->getActiveTenant();
        $query = Cohort::query();

        if ($tenant) {
            $query->where('sae_id', $tenant->id);
        }

        if ($this->selectedProgramId && $this->selectedProgramId !== 'all') {
            $query->where('program_id', $this->selectedProgramId);
        }

        return $query->get();
    }

    public function getIndicatorsData(): array
    {
        $tenant = $this->getActiveTenant();
        $service = app(ReportExportService::class);

        if ($tenant) {
            return $service->getImpactIndicators($tenant, [
                'program_id' => $this->selectedProgramId,
                'cohort_id' => $this->selectedCohortId,
            ]);
        }

        return $service->getImpactIndicators(new Sae(['name' => 'Hubs Africa SAE', 'slug' => 'sae']));
    }

    public function getPrintUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.impact.print', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function getDownloadUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.impact.download', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function getCsvUrl(): string
    {
        $tenant = $this->getActiveTenant();

        return route('sae.reports.impact.csv', [
            'tenant' => $tenant?->slug ?? 'default',
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }

    public function exportCsv(): StreamedResponse
    {
        $tenant = $this->getActiveTenant() ?? new Sae(['name' => 'Hubs Africa', 'slug' => 'hubsafrica']);
        $service = app(ReportExportService::class);
        $data = $this->getIndicatorsData();

        return $service->exportIndicatorsCsv($tenant, $data, [
            'program_id' => $this->selectedProgramId,
            'cohort_id' => $this->selectedCohortId,
        ]);
    }
}
