<?php

namespace App\Providers\Filament;

use App\Filament\Sae\Pages\Tenancy\EditSaeProfile;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
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
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Packstub\FormBuilder\FormBuilderPlugin;

class SaePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('sae')
            ->path('sae')
            ->viteTheme('resources/css/filament/sae/theme.css')
            ->spa(hasPrefetching:true)
            ->login()
            ->colors([
                'primary' => Color::Teal,
            ])
            ->tenant(Sae::class, slugAttribute: 'slug')
            ->tenantRegistration(RegisterSae::class)
            ->tenantProfile(EditSaeProfile::class)
            ->navigationGroups([
                'Pilotage',
                'Portefeuille',
                'Programmes',
                'Suivi & évaluation',
                'Système',
            ])
            ->discoverResources(in: app_path('Filament/Sae/Resources'), for: 'App\Filament\Sae\Resources')
            ->discoverPages(in: app_path('Filament/Sae/Pages'), for: 'App\Filament\Sae\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Sae/Widgets'), for: 'App\Filament\Sae\Widgets')
            ->widgets([])
            ->plugins([
                FormBuilderPlugin::make()
                    ->resource(\App\Filament\Sae\Resources\EvaluationForms\EvaluationFormResource::class)
                    ->navigationGroup('Suivi & évaluation')
                    ->navigationIcon('heroicon-o-clipboard-document-check')
                    ->navigationSort(3),
                FilamentShieldPlugin::make(),
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
