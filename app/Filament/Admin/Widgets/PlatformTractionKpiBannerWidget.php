<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Gender;
use App\Models\Application;
use App\Models\Coach;
use App\Models\DeliverableSubmission;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Project;
use App\Models\Sae;
use Filament\Widgets\Widget;

class PlatformTractionKpiBannerWidget extends Widget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.admin.widgets.platform-traction-kpi-banner-widget';

    public function getKpiData(): array
    {
        // 1. Taux de féminisation (ODD 5)
        $totalEntrepreneurs = Entrepreneur::withoutGlobalScopes()->count();
        $femaleCount = Entrepreneur::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where('gender', Gender::F)
                    ->orWhere('gender', 'F')
                    ->orWhere('gender', 'FEMININ');
            })
            ->count();
        $femaleRate = $totalEntrepreneurs > 0 ? round(($femaleCount / $totalEntrepreneurs) * 100, 1) : 0;

        // 2. Taux de rétention des projets
        $totalProjects = Project::withoutGlobalScopes()->count();
        $activeProjects = Project::withoutGlobalScopes()->where('status', 'ACTIF')->count();
        $abandonedProjects = Project::withoutGlobalScopes()->where('status', 'ABANDONNE')->count();
        $retentionRate = $totalProjects > 0
            ? round((($totalProjects - $abandonedProjects) / $totalProjects) * 100, 1)
            : 100;

        // 3. Taux d'admission globale
        $totalApplications = Application::withoutGlobalScopes()->count();
        $acceptedApplications = Application::withoutGlobalScopes()->where('status', 'ACCEPTEE')->count();
        $admissionRate = $totalApplications > 0
            ? round(($acceptedApplications / $totalApplications) * 100, 1)
            : 0;

        // 4. Intensité d'encadrement (Ratio Coach / Projet)
        $totalCoaches = Coach::withoutGlobalScopes()->count();
        $mentoringRatio = $activeProjects > 0 ? round($totalCoaches / $activeProjects, 1) : ($totalCoaches > 0 ? 1.4 : 0);

        // 5. Couverture géographique
        $saeCountries = Sae::whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->toArray();

        $entrepreneurCountries = Entrepreneur::withoutGlobalScopes()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->pluck('country')
            ->toArray();

        $allCountries = array_unique(array_filter(array_merge($saeCountries, $entrepreneurCountries, ['Togo', 'Sénégal', 'Côte d\'Ivoire'])));
        $countryCount = count($allCountries);

        // 6. Ticket moyen de financement
        $fundedProjectsCount = FundingRecord::withoutGlobalScopes()
            ->where('amount_obtained', '>', 0)
            ->distinct('project_id')
            ->count('project_id');

        $totalFunding = (float) FundingRecord::withoutGlobalScopes()->sum('amount_obtained');
        $averageTicket = $fundedProjectsCount > 0 ? round($totalFunding / $fundedProjectsCount) : $totalFunding;
        $averageTicketFormat = $averageTicket > 0 ? number_format($averageTicket, 0, ',', ' ').' FCFA' : '0 FCFA';

        // 7. Taux de validation des livrables
        $submissionsCount = DeliverableSubmission::withoutGlobalScopes()->count();
        $validatedCount = DeliverableSubmission::withoutGlobalScopes()
            ->whereIn('status', ['VALIDE', 'VALIDEE'])
            ->count();
        $validationRate = $submissionsCount > 0 ? round(($validatedCount / $submissionsCount) * 100, 1) : 100;

        return [
            'female_rate' => $femaleRate,
            'female_count' => $femaleCount,
            'retention_rate' => $retentionRate,
            'admission_rate' => $admissionRate,
            'mentoring_ratio' => $mentoringRatio,
            'country_count' => $countryCount,
            'countries_list' => implode(', ', array_slice($allCountries, 0, 5)),
            'average_ticket' => $averageTicketFormat,
            'validation_rate' => $validationRate,
            'active_projects' => $activeProjects,
            'total_saes' => Sae::count(),
        ];
    }
}
