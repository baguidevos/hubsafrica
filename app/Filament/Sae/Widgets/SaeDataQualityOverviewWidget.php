<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Filament\Sae\Resources\Entrepreneurs\EntrepreneurResource;
use App\Models\Application;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\Project;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaeDataQualityOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Clarté des dossiers & Suivi des anomalies';

    protected ?string $description = 'Distinction entre inscrits sans dossier, candidats en attente et anomalies à corriger pour faciliter la décision.';

    protected function getStats(): array
    {
        $tenant = Filament::getTenant();

        // 1. Inscrits sans projet ni candidature ni enrôlement
        $orphanQuery = Entrepreneur::query()
            ->whereDoesntHave('projects')
            ->whereDoesntHave('applications')
            ->whereDoesntHave('enrollments');

        $orphanTotal = (clone $orphanQuery)->count();
        $orphanActive = (clone $orphanQuery)->where('status', EntrepreneurStatus::ACTIVE->value)->count();
        $orphanCandidat = (clone $orphanQuery)->where('status', EntrepreneurStatus::CANDIDAT->value)->count();

        // 2. Candidats ayant déposé un dossier ou un projet (en attente de sélection)
        $candidatesWithDossier = Entrepreneur::query()
            ->where('status', EntrepreneurStatus::CANDIDAT->value)
            ->where(fn ($q) => $q->whereHas('projects')->orWhereHas('applications'))
            ->count();

        $candidateProjects = Project::query()
            ->where('status', ProjectStatus::CANDIDAT->value)
            ->count();

        $pendingApplications = Application::query()
            ->whereIn('status', [
                ApplicationStatus::SOUMISE->value,
                ApplicationStatus::EN_EXAMEN->value,
            ])
            ->count();

        // 3. Candidatures orphelines sans projet lié
        $applicationsWithoutProject = Application::query()
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereDoesntHave('project'))
            ->count();

        // 4. Formulaires de candidature sans type défini (risque de soumissions ignorées)
        $untypedCandidatureForms = Form::query()
            ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->getKey()))
            ->whereNull('type')
            ->where(function ($q): void {
                $q->whereRaw('LOWER(name) LIKE ?', ['%candidature%'])
                    ->orWhereHas('cohorts');
            })
            ->count();

        $entrepreneursUrl = $tenant ? EntrepreneurResource::getUrl('index', ['tenant' => $tenant]) : null;
        $applicationsUrl = $tenant ? ApplicationResource::getUrl('index', ['tenant' => $tenant]) : null;

        return [
            Stat::make('Inscrits sans projet ni candidature', $orphanTotal)
                ->description("{$orphanActive} en ACTIVE · {$orphanCandidat} en CANDIDAT — À relancer")
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($orphanActive > 0 ? 'danger' : ($orphanTotal > 0 ? 'warning' : 'success'))
                ->url($entrepreneursUrl),

            Stat::make('Candidats avec dossier déposé', $candidatesWithDossier)
                ->description("{$candidateProjects} projet(s) candidat(s) · {$pendingApplications} dossier(s) à évaluer")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info')
                ->url($applicationsUrl),

            Stat::make('Candidatures sans projet lié', $applicationsWithoutProject)
                ->description($applicationsWithoutProject > 0 ? 'Anomalie : projet supprimé ou manquant' : 'Toutes les candidatures ont un projet')
                ->descriptionIcon($applicationsWithoutProject > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($applicationsWithoutProject > 0 ? 'danger' : 'success')
                ->url($applicationsUrl),

            Stat::make('Formulaires candidature non typés', $untypedCandidatureForms)
                ->description($untypedCandidatureForms > 0 ? 'Soumissions ignorées (type CANDIDATURE requis)' : 'Tous les formulaires sont bien configurés')
                ->descriptionIcon($untypedCandidatureForms > 0 ? 'heroicon-m-wrench-screwdriver' : 'heroicon-m-check-badge')
                ->color($untypedCandidatureForms > 0 ? 'danger' : 'success'),
        ];
    }
}
