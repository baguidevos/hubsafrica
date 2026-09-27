<?php

namespace App\Filament\Entrepreneur\Widgets;

use App\Models\CoachingSession;
use App\Models\DeliverableSubmission;
use App\Models\FundingRecord;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EntrepreneurStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $entrepreneur = auth()->user()?->entrepreneur;
        $entrepreneurId = $entrepreneur?->id;

        $project = $entrepreneurId
            ? Project::with('currentLevel')->where('entrepreneur_id', $entrepreneurId)->first()
            : null;

        $niveau = $project?->currentLevel?->name ?: 'Niveau initial';

        $seancesCount = $entrepreneurId
            ? CoachingSession::where(function ($q) use ($entrepreneurId, $project) {
                $q->where('entrepreneur_id', $entrepreneurId);
                if ($project) {
                    $q->orWhere('project_id', $project->id);
                }
            })->count()
            : 0;

        $livrablesValides = $entrepreneurId
            ? DeliverableSubmission::where('entrepreneur_id', $entrepreneurId)
                ->where('status', 'VALIDEE')
                ->count()
            : 0;

        $livrablesTotal = $entrepreneurId
            ? DeliverableSubmission::where('entrepreneur_id', $entrepreneurId)->count()
            : 0;

        $financementObtenu = $project
            ? (float) FundingRecord::where('project_id', $project->id)->whereIn('status', ['ACCORDE', 'DECAISSE', 'INVESTI'])->sum('amount_obtained')
            : 0;
        $financementFormat = number_format($financementObtenu, 0, ',', ' ') . ' FCFA';

        return [
            Stat::make('Mon projet', $project?->name ?: 'En attente')
                ->description('Statut : ' . ($project?->status ?: 'N/A'))
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('primary'),

            Stat::make('Jalon / Niveau', $niveau)
                ->description('Parcours d\'incubation')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),

            Stat::make('Séances de coaching', $seancesCount)
                ->description('Points d\'étape réalisés')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('success'),

            Stat::make('Livrables validés', "{$livrablesValides} / {$livrablesTotal}")
                ->description('Progression méthodologique')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('warning'),

            Stat::make('Financement obtenu', $financementFormat)
                ->description('Mobilisé avec la SAE')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
