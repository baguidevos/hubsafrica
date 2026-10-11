<?php

namespace App\Filament\Coach\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class CoachSaeIdentityWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.coach.widgets.coach-sae-identity-widget';

    public function getSae()
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $tenant->loadMissing('types');
        }

        return $tenant;
    }
}
