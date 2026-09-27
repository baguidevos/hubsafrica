<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CoachPanelProvider;
use App\Providers\Filament\EntrepreneurPanelProvider;
use App\Providers\Filament\SaePanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    CoachPanelProvider::class,
    EntrepreneurPanelProvider::class,
    SaePanelProvider::class,
];
