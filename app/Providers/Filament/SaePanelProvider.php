<?php

namespace App\Providers\Filament;

use App\Filament\Sae\Pages\Auth\RegisterSaeUser;
use App\Filament\Sae\Pages\Tenancy\EditSaeProfile;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
use App\Filament\Sae\Resources\EvaluationForms\EvaluationFormResource;
use App\Models\Sae;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Nagi\FilamentDaisyUiThemes\FilamentDaisyUiThemesPlugin;
use Packstub\FormBuilder\FormBuilderPlugin;

class SaePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('sae')
            ->path('sae')
            ->brandName('Hub Africa')
            ->brandLogo(fn () => asset('images/koworkers.png'))
            ->darkModeBrandLogo(fn () => asset('images/logo.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('images/logo.png'))
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn () => view('filament.sae.hooks.sidebar-tenant-badge'),
            )
            ->viteTheme('resources/css/filament/sae/theme.css')
            ->maxContentWidth(Width::Full)
            ->spa(hasPrefetching: true)
            ->login()
            ->registration(RegisterSaeUser::class)
            ->colors([
                'primary' => Color::Teal,
            ])
            ->tenant(Sae::class, slugAttribute: 'slug')
            ->tenantRegistration(RegisterSae::class)
            ->tenantProfile(EditSaeProfile::class)
            ->navigationGroups([
                'Pilotage',
                'Système',
            ])
            ->discoverClusters(in: app_path('Filament/Sae/Clusters'), for: 'App\\Filament\\Sae\\Clusters')
            ->discoverResources(in: app_path('Filament/Sae/Resources'), for: 'App\\Filament\\Sae\\Resources')
            ->discoverPages(in: app_path('Filament/Sae/Pages'), for: 'App\\Filament\\Sae\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Sae/Widgets'), for: 'App\Filament\Sae\Widgets')
            ->widgets([])
            ->plugins([
                FormBuilderPlugin::make()
                    ->resource(EvaluationFormResource::class),
                FilamentShieldPlugin::make(),
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
