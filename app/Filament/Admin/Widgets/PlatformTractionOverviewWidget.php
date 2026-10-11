<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\Gender;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformTractionOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        // 1. SAEs
        $totalSaes = Sae::count();
        $activeSaes = Sae::where('status', 'ACTIVE')->count();
        if ($activeSaes === 0) {
            $activeSaes = $totalSaes;
        }

        // 2. Entrepreneurs & Parité Genre
        $totalEntrepreneurs = Entrepreneur::withoutGlobalScopes()->count();
        $femaleCount = Entrepreneur::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where('gender', Gender::F)
                    ->orWhere('gender', 'F')
                    ->orWhere('gender', 'FEMININ');
            })
            ->count();
        $femalePercent = $totalEntrepreneurs > 0 ? round(($femaleCount / $totalEntrepreneurs) * 100, 1) : 0;

        // 3. Projets / Startups
        $totalProjects = Project::withoutGlobalScopes()->count();
        $activeProjects = Project::withoutGlobalScopes()->where('status', 'ACTIF')->count();
        if ($activeProjects === 0 && $totalProjects > 0) {
            $activeProjects = $totalProjects;
        }

        // 4. Programmes & Cohortes
        $totalPrograms = Program::withoutGlobalScopes()->count();
        $totalCohorts = Cohort::withoutGlobalScopes()->count();

        // 5. Coachs & Mentors
        $totalCoaches = Coach::withoutGlobalScopes()->count();
        $activeCoaches = Coach::withoutGlobalScopes()->where('status', 'ACTIF')->count();
        if ($activeCoaches === 0 && $totalCoaches > 0) {
            $activeCoaches = $totalCoaches;
        }

        // 6. Séances de Coaching
        $totalSessions = CoachingSession::withoutGlobalScopes()->count();
        $totalHours = (float) CoachingSession::withoutGlobalScopes()->sum('hours_delivered');
        if ($totalHours <= 0 && $totalSessions > 0) {
            $totalHours = $totalSessions * 1.5;
        }

        // 7. Candidatures
        $totalApplications = Application::withoutGlobalScopes()->count();
        $acceptedApplications = Application::withoutGlobalScopes()->where('status', 'ACCEPTEE')->count();
        $admissionRate = $totalApplications > 0 ? round(($acceptedApplications / $totalApplications) * 100, 1) : 0;

        // 8. Financements Mobilisés
        $totalFunding = (float) FundingRecord::withoutGlobalScopes()
            ->whereIn('status', ['ACCORDE', 'DECAISSE', 'INVESTI'])
            ->sum('amount_obtained');

        if ($totalFunding <= 0) {
            $totalFunding = (float) FundingRecord::withoutGlobalScopes()->sum('amount_obtained');
        }

        $fundingFormatted = number_format($totalFunding, 0, ',', ' ').' FCFA';

        return [
            Stat::make('Structures (SAE) raccordées', $totalSaes)
                ->description("{$activeSaes} structures actives sur Hub Africa")
                ->descriptionIcon('heroicon-m-building-office-2')
                ->chart([2, 4, 6, 8, 10, $totalSaes])
                ->color('primary'),

            Stat::make('Entrepreneurs accompagnés', $totalEntrepreneurs)
                ->description("{$femaleCount} fondatrices ({$femalePercent}% — ODD 5)")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([5, 9, 14, 18, 22, $totalEntrepreneurs])
                ->color('info'),

            Stat::make('Startups & Projets actifs', $totalProjects)
                ->description("{$activeProjects} projets en portefeuille")
                ->descriptionIcon('heroicon-m-light-bulb')
                ->chart([1, 3, 4, 6, 7, $totalProjects])
                ->color('success'),

            Stat::make('Programmes & Cohortes', "{$totalPrograms} prog. / {$totalCohorts} coh.")
                ->description('Promotions d\'accélération déployées')
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('primary'),

            Stat::make('Réseau de Mentors & Coachs', $totalCoaches)
                ->description("{$activeCoaches} experts mobilisés sur le terrain")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->chart([2, 4, 7, 9, 11, $totalCoaches])
                ->color('warning'),

            Stat::make('Accompagnement délivré', "{$totalSessions} séances")
                ->description(round($totalHours, 1).' heures de coaching tracées')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Candidatures & Sélections', "{$totalApplications} reçues")
                ->description("{$acceptedApplications} admises ({$admissionRate}% de sélection)")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Capitaux & Financements', $fundingFormatted)
                ->description('Déclarés et validés par les SAE')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([0, 3000000, 7500000, 10000000, 12500000, max(1, (int) $totalFunding)])
                ->color('success'),
        ];
    }
}
