<?php

namespace App\Filament\Sae\Widgets;

use App\Models\Coach;
use Filament\Widgets\Widget;

class CoachWorkloadWidget extends Widget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected string $view = 'filament.sae.widgets.coach-workload-widget';

    public function getCoachWorkloads(): array
    {
        $coaches = Coach::withCount(['assignments' => function ($q) {
            $q->where('status', 'ACTIF');
        }])
            ->where('status', 'ACTIF')
            ->limit(6)
            ->get();

        return $coaches->map(function ($c) {
            $capacite = $c->capacity ?: 5;
            $actifs = $c->assignments_count;
            $pct = $capacite > 0 ? min(100, round(($actifs / $capacite) * 100)) : 0;

            return [
                'id' => $c->id,
                'name' => $c->name,
                'specialty' => $c->specialty ?: 'Généraliste',
                'actifs' => $actifs,
                'capacite' => $capacite,
                'pct' => $pct,
                'isOverloaded' => $actifs >= $capacite,
            ];
        })->toArray();
    }
}
