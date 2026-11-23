<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PlatformDataQualityOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Santé & Clarté des Données Plateforme';

    protected ?string $description = 'Visibilité globale sur les comptes sans projet, le funnel candidats et les anomalies de données.';

    protected function getStats(): array
    {
        // 1. Inscrits sans projet ni candidature ni enrôlement
        $orphanQuery = Entrepreneur::withoutGlobalScopes()
            ->whereDoesntHave('projects', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('applications', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('enrollments', fn ($q) => $q->withoutGlobalScopes());

        $orphanTotal = (clone $orphanQuery)->count();
        $orphanActive = (clone $orphanQuery)->where('status', EntrepreneurStatus::ACTIVE->value)->count();
        $orphanCandidat = (clone $orphanQuery)->where('status', EntrepreneurStatus::CANDIDAT->value)->count();

        // 2. Candidats avec dossier déposé
        $candidatesWithDossier = Entrepreneur::withoutGlobalScopes()
            ->where('status', EntrepreneurStatus::CANDIDAT->value)
            ->where(fn ($q) => $q
                ->whereHas('projects', fn ($pq) => $pq->withoutGlobalScopes())
                ->orWhereHas('applications', fn ($aq) => $aq->withoutGlobalScopes()))
            ->count();

        $candidateProjects = Project::withoutGlobalScopes()
            ->where('status', ProjectStatus::CANDIDAT->value)
            ->count();

        $pendingApplications = Application::withoutGlobalScopes()
            ->whereIn('status', [
                ApplicationStatus::SOUMISE->value,
                ApplicationStatus::EN_EXAMEN->value,
            ])
            ->count();

        // 3. Candidatures sans projet lié
        $applicationsWithoutProject = Application::withoutGlobalScopes()
            ->where(fn ($q) => $q
                ->whereNull('project_id')
                ->orWhereDoesntHave('project', fn ($pq) => $pq->withoutGlobalScopes()))
            ->count();

        // 4. Formulaires de candidature sans type
        $untypedCandidatureForms = Form::withoutGlobalScopes()
            ->whereNull('type')
            ->where(function ($q): void {
                $q->whereRaw('LOWER(name) LIKE ?', ['%candidature%'])
                    ->orWhereHas('cohorts', fn ($cq) => $cq->withoutGlobalScopes());
            })
            ->count();

        return [
            Stat::make('Comptes sans projet ni candidature', $orphanTotal)
                ->description("{$orphanActive} en ACTIVE · {$orphanCandidat} en CANDIDAT")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($orphanActive > 0 ? 'danger' : ($orphanTotal > 0 ? 'warning' : 'success'))
                ->url('/hubs-hq/entrepreneurs'),

            Stat::make('Candidats avec dossier déposé', $candidatesWithDossier)
                ->description("{$candidateProjects} projet(s) candidat(s) · {$pendingApplications} à traiter")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),

            Stat::make('Candidatures sans projet lié', $applicationsWithoutProject)
                ->description($applicationsWithoutProject > 0 ? 'Anomalie : projet supprimé ou manquant' : 'Aucune anomalie détectée')
                ->descriptionIcon($applicationsWithoutProject > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($applicationsWithoutProject > 0 ? 'danger' : 'success'),

            Stat::make('Formulaires candidature non typés', $untypedCandidatureForms)
                ->description($untypedCandidatureForms > 0 ? 'Type CANDIDATURE manquant' : 'Configuration saine')
                ->descriptionIcon($untypedCandidatureForms > 0 ? 'heroicon-m-wrench-screwdriver' : 'heroicon-m-check-badge')
                ->color($untypedCandidatureForms > 0 ? 'danger' : 'success'),
        ];
    }
}
