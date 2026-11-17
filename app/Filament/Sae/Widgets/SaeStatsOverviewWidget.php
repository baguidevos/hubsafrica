<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachingSessionStatus;
use App\Enums\CoachStatus;
use App\Enums\CohortStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\FundingStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\DeliverableSubmission;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Program;
use App\Models\Project;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SaeStatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // 1. Portefeuille & Inclusion (exclut les dossiers sans projet ni enrôlement)
        $accompaniedEntrepreneurs = Entrepreneur::query()
            ->where(fn ($q) => $q->whereHas('projects')->orWhereHas('enrollments'));

        $entrepreneursActifs = (clone $accompaniedEntrepreneurs)
            ->where('status', EntrepreneurStatus::ACTIVE->value)
            ->count();
        $entrepreneursDiplomes = (clone $accompaniedEntrepreneurs)
            ->where('status', EntrepreneurStatus::DIPLOME->value)
            ->count();
        $porteeEntrepreneurs = (clone $accompaniedEntrepreneurs)
            ->whereIn('status', [
                EntrepreneurStatus::ACTIVE->value,
                EntrepreneurStatus::DIPLOME->value,
            ])->count();
        $femmesEntrepreneurs = (clone $accompaniedEntrepreneurs)
            ->whereIn('status', [
                EntrepreneurStatus::ACTIVE->value,
                EntrepreneurStatus::DIPLOME->value,
            ])->where('gender', 'F')->count();
        $tauxFeminisation = $porteeEntrepreneurs > 0
            ? round(($femmesEntrepreneurs / $porteeEntrepreneurs) * 100)
            : 0;

        $projetsActifs = Project::where('status', ProjectStatus::ACTIF->value)->count();
        $emploisDirects = (int) Project::whereIn('status', [
            ProjectStatus::ACTIF->value,
            ProjectStatus::DIPLOME->value,
        ])->sum('team_size');
        $projetsCandidats = Project::where('status', ProjectStatus::CANDIDAT->value)->count();

        $programmesActifs = Program::whereIn('status', [ProgramStatus::ACTIF->value, ProgramStatus::LANCE->value])->count();
        $programmesEnAppel = Program::where('status', ProgramStatus::OUVERT_CANDIDATURES->value)->count();
        $candidaturesEnAppel = Application::where('status', '!=', ApplicationStatus::BROUILLON->value)
            ->whereHas('program', fn ($q) => $q->where('status', ProgramStatus::OUVERT_CANDIDATURES->value))
            ->count();
        $cohortesEnCours = Cohort::where('status', CohortStatus::EN_COURS->value)->count();
        $cohortesOuvertes = Cohort::where('status', CohortStatus::OUVERTE->value)->count();

        // 2. Pipeline Candidatures
        $candidaturesATraiter = Application::whereIn('status', [
            ApplicationStatus::SOUMISE->value,
            ApplicationStatus::EN_EXAMEN->value,
        ])->count();
        $candidaturesRecues = Application::where('status', '!=', ApplicationStatus::BROUILLON->value)->count();
        $admissions = Application::where('status', ApplicationStatus::ACCEPTEE->value)->count();

        // 3. Suivi Coaching & Livrables
        $coachsActifs = Coach::whereIn('status', [CoachStatus::ACTIF->value, CoachStatus::ACTIVE->value])->count();
        $heuresCoaching = round((float) CoachingSession::where('status', CoachingSessionStatus::REALISEE->value)
            ->selectRaw('SUM(COALESCE(hours_delivered, duration / 60.0)) as total_hours')
            ->value('total_hours'), 1);

        $projetsSansCoach = Project::where('status', ProjectStatus::ACTIF->value)
            ->whereDoesntHave('assignments', function ($q) {
                $q->where('status', CoachAssignmentStatus::ACTIF->value);
            })
            ->count();

        $livrablesAEvaluer = DeliverableSubmission::whereIn('status', [
            DeliverableSubmissionStatus::SOUMIS->value,
            DeliverableSubmissionStatus::SOUMISE->value,
            DeliverableSubmissionStatus::EN_EVALUATION->value,
            DeliverableSubmissionStatus::EN_EXAMEN->value,
        ])->count();
        $livrablesEnRetard = DeliverableSubmission::where('is_late', true)->count();

        $projetsBloques = Project::where('status', ProjectStatus::BLOQUE->value)->count();
        $projetsStagnants = Project::where('status', ProjectStatus::STAGNANT->value)->count();
        $abandons = Project::where('status', ProjectStatus::ABANDONNE->value)->count();

        // 4. Impact & Financements
        $montantsDecaisses = (float) FundingRecord::whereIn('status', [
            FundingStatus::DECAISSE->value,
            FundingStatus::INVESTI->value,
        ])->selectRaw('SUM(COALESCE(NULLIF(amount_obtained, 0), amount_invested, 0)) as total')
            ->value('total');
        $montantsAccordes = (float) FundingRecord::where('status', FundingStatus::ACCORDE->value)
            ->sum('amount_obtained');

        $decaissesFormat = number_format($montantsDecaisses, 0, ',', ' ').' FCFA';
        $accordesFormat = number_format($montantsAccordes, 0, ',', ' ').' FCFA';

        return [
            Stat::make('Entrepreneurs accompagnés', $entrepreneursActifs)
                ->description("{$entrepreneursDiplomes} diplômé(s) · {$tauxFeminisation}% femmes")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Projets en incubation', $projetsActifs)
                ->description("{$projetsCandidats} candidat(s)")
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('primary'),

            Stat::make('Programmes actifs', $programmesActifs)
                ->description("{$cohortesEnCours} cohorte(s) en cours · {$cohortesOuvertes} ouverte(s)")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('info'),

            Stat::make('Appels à candidatures', $programmesEnAppel)
                ->description("{$candidaturesEnAppel} candidature(s) · {$cohortesOuvertes} cohorte(s) ouverte(s)")
                ->descriptionIcon('heroicon-m-megaphone')
                ->color($programmesEnAppel > 0 ? 'primary' : 'gray')
                ->url(fn (): ?string => Filament::getTenant() ? ApplicationResource::getUrl('index', [
                    'tenant' => Filament::getTenant(),
                    'filters' => [
                        'open_call_programs' => ['isActive' => true],
                    ],
                ]) : null),

            Stat::make('Candidatures à traiter', $candidaturesATraiter)
                ->description("{$candidaturesRecues} reçues · {$admissions} admise(s)")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color($candidaturesATraiter > 0 ? 'warning' : 'success'),

            Stat::make('Coachs mobilisés', $coachsActifs)
                ->description("{$heuresCoaching}h de coaching réalisées")
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('info'),

            Stat::make('Projets sans coach', $projetsSansCoach)
                ->description($projetsSansCoach > 0 ? 'Affectation requise' : 'Tous suivis')
                ->descriptionIcon('heroicon-m-user-minus')
                ->color($projetsSansCoach > 0 ? 'warning' : 'success'),

            Stat::make('Livrables à évaluer', $livrablesAEvaluer)
                ->description($livrablesEnRetard > 0 ? "{$livrablesEnRetard} déposé(s) en retard" : 'Aucun retard signalé')
                ->descriptionIcon('heroicon-m-document-check')
                ->color($livrablesAEvaluer > 0 || $livrablesEnRetard > 0 ? 'warning' : 'success'),

            Stat::make('Projets bloqués', $projetsBloques)
                ->description("{$projetsStagnants} stagnant(s) · {$abandons} abandon(s)")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($projetsBloques > 0 ? 'danger' : ($projetsStagnants > 0 ? 'warning' : 'success')),

            Stat::make('Financements décaissés', $decaissesFormat)
                ->description("{$accordesFormat} accordés en cours")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
