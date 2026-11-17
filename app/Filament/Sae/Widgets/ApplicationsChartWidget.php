<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Filament\Widgets\ChartWidget;

class ApplicationsChartWidget extends ChartWidget
{
    protected static ?int $sort = 5;

    protected ?string $heading = 'Candidatures — 6 derniers mois';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
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
            $recues[] = Application::where('status', '!=', ApplicationStatus::BROUILLON->value)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $admises[] = Application::where('status', ApplicationStatus::ACCEPTEE->value)
                ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                    $q->whereBetween('reviewed_at', [$startOfMonth, $endOfMonth])
                        ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
                            $q2->whereNull('reviewed_at')
                                ->whereBetween('updated_at', [$startOfMonth, $endOfMonth]);
                        });
                })
                ->count();
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
