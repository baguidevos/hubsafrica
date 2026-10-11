<?php

namespace App\Filament\Sae\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class SaeIdentityWidget extends Widget
{
    protected static ?int $sort = 0;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.sae.widgets.sae-identity-widget';

    public function getSae()
    {
        $tenant = Filament::getTenant();

        if ($tenant) {
            $tenant->loadMissing('types');
        }

        return $tenant;
    }
}
