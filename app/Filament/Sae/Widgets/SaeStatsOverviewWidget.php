<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachStatus;
use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\FundingStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Coach;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Program;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaeStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $entrepreneursActifs = Entrepreneur::where('status', EntrepreneurStatus::ACTIVE->value)->count();
        $projetsActifs = Project::where('status', ProjectStatus::ACTIF->value)->count();
        $programmesActifs = Program::whereIn('status', [ProgramStatus::ACTIF->value, ProgramStatus::LANCE->value])->count();
        $cohortesActives = Cohort::where('status', CohortStatus::EN_COURS->value)->count();
        $coachsActifs = Coach::whereIn('status', [CoachStatus::ACTIF->value, CoachStatus::ACTIVE->value])->count();

        $candidatures = Application::where('status', '!=', ApplicationStatus::BROUILLON->value)->count();
        $admissions = Application::where('status', ApplicationStatus::ACCEPTEE->value)->count();

        $abandons = Project::where('status', ProjectStatus::ABANDONNE->value)->count();
        $projetsSansCoach = Project::where('status', ProjectStatus::ACTIF->value)
            ->whereDoesntHave('assignments', function ($q) {
                $q->where('status', CoachAssignmentStatus::ACTIF->value);
            })
            ->count();
        $projetsBloques = Project::where('status', ProjectStatus::BLOQUE->value)->count();
        $projetsStagnants = Project::where('status', ProjectStatus::STAGNANT->value)->count();

        $montantsLeves = (float) FundingRecord::whereIn('status', [
            FundingStatus::ACCORDE->value,
            FundingStatus::DECAISSE->value,
            FundingStatus::INVESTI->value,
        ])->sum('amount_obtained');
        $montantsFormat = number_format($montantsLeves, 0, ',', ' ').' FCFA';

        $blocageDescription = $projetsBloques > 0
            ? ($projetsStagnants > 0 ? "Point de blocage (+{$projetsStagnants} stagnant(s))" : 'Point de blocage signalé')
            : ($projetsStagnants > 0 ? "{$projetsStagnants} projet(s) stagnant(s)" : 'Aucun projet bloqué');

        return [
            Stat::make('Entrepreneurs accompagnés', $entrepreneursActifs)
                ->description('Dossiers actifs dans la SAE')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Projets actifs', $projetsActifs)
                ->description('Portefeuille en cours')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('primary'),

            Stat::make('Programmes actifs', $programmesActifs)
                ->description("{$cohortesActives} cohortes actives")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),

            Stat::make('Coachs mobilisés', $coachsActifs)
                ->description('Réseau d\'experts actifs')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Candidatures reçues', $candidatures)
                ->description("{$admissions} admises")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),

            Stat::make('Projets sans coach', $projetsSansCoach)
                ->description($projetsSansCoach > 0 ? 'Affectation requise' : 'Tous suivis')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($projetsSansCoach > 0 ? 'warning' : 'success'),

            Stat::make('Projets bloqués', $projetsBloques)
                ->description($blocageDescription)
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($projetsBloques > 0 ? 'danger' : ($projetsStagnants > 0 ? 'warning' : 'success')),

            Stat::make('Abandons', $abandons)
                ->description('Sorties prématurées')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('gray'),

            Stat::make('Financements mobilisés', $montantsFormat)
                ->description('Accordés, décaissés et investis')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
