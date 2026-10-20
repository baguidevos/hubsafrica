<?php

namespace App\Providers\Filament;

use App\Enums\ApplicationStatus;
use App\Enums\CoachJoinRequestStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Pages\Auth\RegisterSaeUser;
use App\Filament\Sae\Pages\Tenancy\EditSaeProfile;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Filament\Sae\Resources\CoachJoinRequests\CoachJoinRequestResource;
use App\Filament\Sae\Resources\DeliverableSubmissions\DeliverableSubmissionResource;
use App\Filament\Sae\Resources\EvaluationForms\EvaluationFormResource;
use App\Filament\Sae\Resources\Projects\ProjectResource;
use App\Models\Application;
use App\Models\CoachJoinRequest;
use App\Models\DeliverableSubmission;
use App\Models\Project;
use App\Models\Sae;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\Action;
use Filament\Facades\Filament;
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
use HoceineEl\QuickActionDock\Enums\DockLayout;
use HoceineEl\QuickActionDock\Enums\DockPosition;
use HoceineEl\QuickActionDock\Enums\DockStyle;
use HoceineEl\QuickActionDock\QuickActionDockPlugin;
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
            ->passwordReset()
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
                // FilamentShieldPlugin::make(),
                FilamentDaisyUiThemesPlugin::make()
                    ->allThemes(),
                QuickActionDockPlugin::make()
                    ->position(DockPosition::BottomEnd)
                    ->style(DockStyle::Outline)
                    ->layout(DockLayout::Bar)
                    ->triggerIcon('heroicon-o-view-columns')
                    ->triggerLabel('Pipelines & Kanbans')
                    ->showLabels()
                    ->keyBindings(['mod+.'])
                    ->group('Pipelines & Kanbans', fn (): array => [
                        Action::make('kanbanApplications')
                            ->label('Pipeline Candidatures')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->color('warning')
                            ->keyBindings(['mod+shift+c'])
                            ->badge(fn () => Filament::getTenant() ? Application::query()->where('status', ApplicationStatus::SOUMISE)->count() ?: null : null)
                            ->badgeColor('warning')
                            ->url(fn (): ?string => Filament::getTenant() ? ApplicationResource::getUrl('kanban', ['tenant' => Filament::getTenant()]) : null),

                        Action::make('kanbanProjects')
                            ->label('Paliers de Maturité')
                            ->icon('heroicon-o-briefcase')
                            ->color('info')
                            ->keyBindings(['mod+shift+p'])
                            ->badge(fn () => Filament::getTenant() ? Project::query()->where('status', ProjectStatus::ACTIF)->count() ?: null : null)
                            ->badgeColor('info')
                            ->url(fn (): ?string => Filament::getTenant() ? ProjectResource::getUrl('kanban', ['tenant' => Filament::getTenant()]) : null),

                        Action::make('kanbanDeliverables')
                            ->label('Suivi des Livrables')
                            ->icon('heroicon-o-check-badge')
                            ->color('success')
                            ->keyBindings(['mod+shift+l'])
                            ->badge(fn () => Filament::getTenant() ? DeliverableSubmission::query()->whereIn('status', [DeliverableSubmissionStatus::SOUMIS, DeliverableSubmissionStatus::SOUMISE, DeliverableSubmissionStatus::EN_EVALUATION])->count() ?: null : null)
                            ->badgeColor('warning')
                            ->url(fn (): ?string => Filament::getTenant() ? DeliverableSubmissionResource::getUrl('kanban', ['tenant' => Filament::getTenant()]) : null),

                        Action::make('kanbanCoachRequests')
                            ->label('Recrutement Coachs')
                            ->icon('heroicon-o-academic-cap')
                            ->color('purple')
                            ->keyBindings(['mod+shift+k'])
                            ->badge(fn () => Filament::getTenant() ? CoachJoinRequest::query()->where('status', CoachJoinRequestStatus::PENDING)->count() ?: null : null)
                            ->badgeColor('danger')
                            ->url(fn (): ?string => Filament::getTenant() ? CoachJoinRequestResource::getUrl('kanban', ['tenant' => Filament::getTenant()]) : null),
                    ]),
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
