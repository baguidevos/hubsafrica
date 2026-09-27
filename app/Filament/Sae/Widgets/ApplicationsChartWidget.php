<?php

namespace App\Filament\Sae\Widgets;

use App\Models\Application;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ApplicationsChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;
    protected ?string $heading = 'Candidatures — 6 derniers mois';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 2,
    ];

    protected function getData(): array
    {
        $months = [];
        $recues = [];
        $admises = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = ucfirst($date->translatedFormat('M y'));
            $startOfMonth = $date->copy()->startOfMonth();
            $endOfMonth = $date->copy()->endOfMonth();

            $months[] = $monthLabel;
            $recues[] = Application::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $admises[] = Application::where('status', 'ACCEPTEE')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
        }

        // Si la base est encore jeune et vide sur les 6 mois, injecter les valeurs actuelles
        if (array_sum($recues) === 0) {
            $total = Application::count();
            $adm = Application::where('status', 'ACCEPTEE')->count();
            $recues[5] = max($total, 1);
            $admises[5] = max($adm, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Reçues',
                    'data' => $recues,
                    'backgroundColor' => '#0d9488',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Admises',
                    'data' => $admises,
                    'backgroundColor' => '#f59e0b',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
