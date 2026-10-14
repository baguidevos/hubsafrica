<?php

namespace App\Providers;

use App\Listeners\HandleCandidatureSubmission;
use App\Listeners\HandleEvaluationSubmission;
use App\Models\Application;
use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\DeliverableSubmission;
use App\Notifications\FilamentResetPassword;
use App\Observers\ApplicationObserver;
use App\Observers\CoachAssignmentObserver;
use App\Observers\CoachingSessionObserver;
use App\Observers\DeliverableSubmissionObserver;
use Carbon\CarbonImmutable;
use Filament\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Head\Facades\Head;
use Packstub\FormBuilder\Events\SubmissionReceived;

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

        Event::listen(
            SubmissionReceived::class,
            HandleEvaluationSubmission::class,
        );

        Event::listen(
            SubmissionReceived::class,
            HandleCandidatureSubmission::class,
        );

        Application::observe(ApplicationObserver::class);
        CoachAssignment::observe(CoachAssignmentObserver::class);
        CoachingSession::observe(CoachingSessionObserver::class);
        DeliverableSubmission::observe(DeliverableSubmissionObserver::class);
    }

    /**
     * Configure default SEO metadata using laravel/head.
     */
    protected function configureHeadDefaults(): void
    {
        Head::defaults(function ($head) {
            $head->title(config('hubafrica.seo.title', 'Hub Africa — Plateforme Panafricaine d\'Accompagnement'))
                ->description(config('hubafrica.seo.description', 'Plateforme institutionnelle d\'accompagnement et d\'incubation en Afrique.'))
                ->applicationName(config('hubafrica.name', 'Hub Africa'))
                ->themeColor(config('hubafrica.theme.primary', '#064e3b'))
                ->canonical()
                ->og(
                    type: 'website',
                    siteName: config('hubafrica.name', 'Hub Africa'),
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
