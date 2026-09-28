<?php

namespace App\Filament\Coach\Widgets;

use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\DeliverableSubmission;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CoachStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $user = auth()->user();
        $coach = $user?->coach;
        $coachId = $coach?->id;

        $projetsActifs = $coachId
            ? CoachAssignment::where('coach_id', $coachId)->where('status', 'ACTIF')->count()
            : 0;

        $projectIds = $coachId
            ? CoachAssignment::where('coach_id', $coachId)->where('status', 'ACTIF')->pluck('project_id')
            : collect();

        $seancesMois = $coachId
            ? CoachingSession::where('coach_id', $coachId)
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->count()
            : 0;

        $totalMinutes = $coachId
            ? CoachingSession::where('coach_id', $coachId)->sum('duration')
            : 0;
        $totalHeures = round($totalMinutes / 60);

        $livrablesEnAttente = DeliverableSubmission::whereIn('project_id', $projectIds)
            ->whereIn('status', ['SOUMISE', 'EN_EXAMEN'])
            ->count();

        return [
            Stat::make('Mes projets suivis', $projetsActifs)
                ->description('Accompagnements actifs')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('primary'),

            Stat::make('Séances ce mois-ci', $seancesMois)
                ->description('Réalisées ou planifiées')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Heures d\'accompagnement', "{$totalHeures} h")
                ->description('Volume global dispensé')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Livrables à évaluer', $livrablesEnAttente)
                ->description($livrablesEnAttente > 0 ? 'En attente de notation' : 'Tous évalués')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($livrablesEnAttente > 0 ? 'warning' : 'success'),
        ];
    }
}
