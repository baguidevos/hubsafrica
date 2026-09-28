<?php

namespace App\Filament\Sae\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;

class PortfolioSectorChartWidget extends ChartWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Répartition par secteur';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected function getData(): array
    {
        $sectors = Project::whereNotNull('sector')
            ->selectRaw('sector, count(*) as count')
            ->groupBy('sector')
            ->orderByDesc('count')
            ->limit(6)
            ->pluck('count', 'sector')
            ->toArray();

        if (empty($sectors)) {
            $sectors = [
                'Agriculture' => 3,
                'Fintech' => 2,
                'Énergie' => 1,
                'Santé' => 1,
            ];
        }

        $colors = ['#0d9488', '#f59e0b', '#8b5cf6', '#16a34a', '#e11d48', '#0ea5e9'];

        return [
            'datasets' => [
                [
                    'label' => 'Projets',
                    'data' => array_values($sectors),
                    'backgroundColor' => array_slice($colors, 0, count($sectors)),
                ],
            ],
            'labels' => array_keys($sectors),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
