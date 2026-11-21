<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationAudit;
use App\Models\AccompanimentTool;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\DataRoomDocument;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Opportunity;
use App\Models\Partner;
use App\Models\Program;
use App\Models\Project;
use App\Models\Resource;
use App\Models\Sae;
use App\Models\User;
use App\Notifications\FilamentResetPassword;
use App\Observers\ApplicationObserver;
use App\Observers\AuditModelObserver;
use App\Observers\CoachAssignmentObserver;
use App\Observers\CoachingSessionObserver;
use App\Observers\DeliverableSubmissionObserver;
use Carbon\CarbonImmutable;
use Filament\Auth\Notifications\ResetPassword;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsIconAlias;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Head\Facades\Head;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ResetPassword::class,
            FilamentResetPassword::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureHeadDefaults();

        Application::observe(ApplicationObserver::class);
        CoachAssignment::observe(CoachAssignmentObserver::class);
        CoachingSession::observe(CoachingSessionObserver::class);
        DeliverableSubmission::observe(DeliverableSubmissionObserver::class);

        $this->configureAuditLogging();

        $this->configureFilamentIcons();
        $this->configureFilamentAuthHooks();
        $this->configureFilamentLivewireHooks();
    }

    /**
     * Personnalisation des icônes de la barre latérale rétractable sur tous les panels Filament.
     */
    protected function configureFilamentIcons(): void
    {
        FilamentIcon::register([
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON => Heroicon::OutlinedChevronDoubleLeft,
            PanelsIconAlias::SIDEBAR_COLLAPSE_BUTTON_RTL => Heroicon::OutlinedChevronDoubleRight,
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON => Heroicon::OutlinedChevronDoubleRight,
            PanelsIconAlias::SIDEBAR_EXPAND_BUTTON_RTL => Heroicon::OutlinedChevronDoubleLeft,
        ]);
    }

    /**
     * Enregistrement automatique du journal d'audit pour les entités métier et l'authentification.
     */
    protected function configureAuditLogging(): void
    {
        $auditedModels = [
            AccompanimentTool::class,
            Application::class,
            Coach::class,
            CoachAssignment::class,
            CoachingSession::class,
            Cohort::class,
            Course::class,
            DataRoomDocument::class,
            DeliverableSubmission::class,
            Enrollment::class,
            Entrepreneur::class,
            FundingRecord::class,
            Opportunity::class,
            Partner::class,
            Program::class,
            Project::class,
            Resource::class,
            Sae::class,
            User::class,
        ];

        foreach ($auditedModels as $modelClass) {
            $modelClass::observe(AuditModelObserver::class);
        }

        Event::listen(
            [Login::class, Logout::class, PasswordReset::class],
            LogAuthenticationAudit::class,
        );
    }

    /**
     * Personnalisation visuelle globale des pages d'authentification Filament.
     */
    protected function configureFilamentAuthHooks(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn () => view('filament.auth.styles'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_LAYOUT_START,
            fn () => view('filament.auth.header'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_PAGE_START,
            fn () => view('filament.auth.card-header'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_LAYOUT_END,
            fn () => view('filament.auth.footer'),
        );
    }

    /**
     * Interception globale des erreurs de polling et de session pour Livewire dans Filament.
     */
    protected function configureFilamentLivewireHooks(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn () => view('filament.hooks.livewire-error-handler'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_START,
            fn () => view('filament.hooks.impersonation-banner'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn () => view('filament.hooks.impersonation-banner'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_START,
            fn () => view('filament.hooks.sidebar-floating-toggle'),
        );
    }

    /**
     * Configure default SEO metadata using laravel/head.
     */
    protected function configureHeadDefaults(): void
    {
        Head::defaults(function ($head) {
            $head->title(config('hubsafrica.seo.title', 'Hubs Africa — Plateforme Panafricaine d\'Accompagnement'))
                ->description(config('hubsafrica.seo.description', 'Plateforme institutionnelle d\'accompagnement et d\'incubation en Afrique.'))
                ->applicationName(config('hubsafrica.name', 'Hubs Africa'))
                ->themeColor(config('hubsafrica.theme.primary', '#064e3b'))
                ->canonical()
                ->og(
                    type: 'website',
                    siteName: config('hubsafrica.name', 'Hubs Africa'),
                    locale: 'fr_FR'
                )
                ->twitter(card: 'summary_large_image');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
