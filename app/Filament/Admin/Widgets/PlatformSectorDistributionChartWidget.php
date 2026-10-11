<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;

class PlatformSectorDistributionChartWidget extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Répartition sectorielle des startups';

    protected ?string $maxHeight = '320px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected function getData(): array
    {
        $sectors = Project::withoutGlobalScopes()
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->selectRaw('sector, count(*) as count')
            ->groupBy('sector')
            ->orderByDesc('count')
            ->limit(7)
            ->pluck('count', 'sector')
            ->toArray();

        if (empty($sectors)) {
            $sectors = [
                'AgriTech / Agro-industrie' => 3,
                'Commerce / E-commerce' => 2,
                'Fintech & Inclusion' => 2,
                'Tech / Numérique' => 1,
                'Énergie & Climat' => 1,
            ];
        }

        $colors = [
            '#0284c7', // Bleu Océan
            '#10b981', // Vert Émeraude
            '#f59e0b', // Ambre Panafricain
            '#8b5cf6', // Violet Tech
            '#ec4899', // Rose Impact
            '#14b8a6', // Teal
            '#f97316', // Orange
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Startups',
                    'data' => array_values($sectors),
                    'backgroundColor' => array_slice($colors, 0, count($sectors)),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
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
