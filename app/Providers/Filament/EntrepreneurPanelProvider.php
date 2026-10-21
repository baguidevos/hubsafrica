<?php

namespace App\Providers\Filament;

use App\Models\Sae;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Nagi\FilamentDaisyUiThemes\FilamentDaisyUiThemesPlugin;

class EntrepreneurPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('entrepreneur')
            ->path('portal')
            ->brandName('Hubs Africa')
            ->brandLogo(fn () => asset('images/logo2.png'))
            ->darkModeBrandLogo(fn () => asset('images/dark_big.png'))
            ->brandLogoHeight('3.5rem')
            ->favicon(asset('favicon/favicon.ico'))
            ->viteTheme('resources/css/filament/entrepreneur/theme.css')
            ->maxContentWidth(Width::Full)
            ->login()
            ->passwordReset()
            ->spa(hasPrefetching: true)
            ->colors([
                'primary' => Color::Emerald,
            ])
            ->tenant(Sae::class, slugAttribute: 'slug')
            ->navigationGroups([
                'Mon espace',
                'Mes dépôts',
                'Aide',
            ])
            ->discoverResources(in: app_path('Filament/Entrepreneur/Resources'), for: 'App\Filament\Entrepreneur\Resources')
            ->discoverPages(in: app_path('Filament/Entrepreneur/Pages'), for: 'App\Filament\Entrepreneur\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Entrepreneur/Widgets'), for: 'App\Filament\Entrepreneur\Widgets')
            ->widgets([])
            ->plugins([
                FilamentDaisyUiThemesPlugin::make()
                    ->allThemes(),
            ])
            ->databaseNotifications()
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
