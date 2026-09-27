<?php

namespace App\Filament\Sae\Widgets;

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
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $entrepreneursActifs = Entrepreneur::where('status', 'ACTIVE')->count();
        $projetsActifs = Project::where('status', 'ACTIF')->count();
        $programmesActifs = Program::where('status', 'ACTIF')->count();
        $cohortesActives = Cohort::where('status', 'EN_COURS')->count();
        $coachsActifs = Coach::where('status', 'ACTIF')->count();

        $candidatures = Application::count();
        $admissions = Application::where('status', 'ACCEPTEE')->count();

        $abandons = Project::where('status', 'ABANDONNE')->count();
        $projetsSansCoach = Project::where('status', 'ACTIF')->whereDoesntHave('assignments', function ($q) {
            $q->where('status', 'ACTIF');
        })->count();
        $projetsBloques = Project::where('status', 'BLOQUE')->count();

        $montantsLeves = (float) FundingRecord::whereIn('status', ['ACCORDE', 'DECAISSE', 'INVESTI'])->sum('amount_obtained');
        $montantsFormat = number_format($montantsLeves, 0, ',', ' ') . ' FCFA';

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
                ->description($projetsBloques > 0 ? 'Point de blocage signalé' : 'Aucun projet bloqué')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($projetsBloques > 0 ? 'danger' : 'success'),

            Stat::make('Abandons', $abandons)
                ->description('Sorties prématurées')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color('gray'),

            Stat::make('Financements mobilisés', $montantsFormat)
                ->description('Déclarés et validés')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
