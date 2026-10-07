<?php

namespace App\Filament\Sae\Pages;

use App\Enums\CoachingSessionStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\DeliverableTarget;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\Project;
use App\Services\AccompanimentTemplateService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AccompanimentMonitoringDashboard extends Page
{
    protected string $view = 'filament.sae.pages.accompaniment-monitoring-dashboard';

    protected static string|\UnitEnum|null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Tableau de bord M&E (SAE-Acte)';

    protected static ?string $title = 'Tableau de bord de Suivi & Évaluation (SAE-Acte / Afric\'innov)';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 1;

    public ?string $selectedCohortId = null;

    public string $activeTab = 'startups'; // 'startups' | 'programme' | 'phases' | 'alerts'

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applyMethodologyTemplate')
                ->label('Appliquer un pack méthodologique')
                ->icon('heroicon-o-sparkles')
                ->color('emerald')
                ->slideOver()
                ->modalHeading('Déployer une méthodologie d\'accompagnement')
                ->modalDescription('Initialise automatiquement les 6 phases, les livrables startups, les livrables SAEI et synchronise le tableau de bord.')
                ->form([
                    Select::make('template_key')
                        ->label('Modèle d\'accompagnement')
                        ->options(fn (AccompanimentTemplateService $service) => $service->asSelectOptions())
                        ->default('startup-loop-africinnov')
                        ->required(),

                    Select::make('cohort_id')
                        ->label('Cohorte cible')
                        ->options(fn () => Cohort::pluck('name', 'id'))
                        ->default(fn () => $this->selectedCohortId)
                        ->required(),
                ])
                ->action(function (array $data, AccompanimentTemplateService $service) {
                    $sae = filament()->getTenant();
                    $cohort = Cohort::findOrFail($data['cohort_id']);
                    $program = $cohort->program;

                    if (! $program) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('La cohorte doit être rattachée à un programme.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $summary = $service->apply($data['template_key'], $sae, $program, $cohort);

                    $this->selectedCohortId = (string) $cohort->id;

                    Notification::make()
                        ->title('Méthodologie déployée avec succès !')
                        ->success()
                        ->body("La cohorte {$cohort->name} a été synchronisée avec {$summary['phases']} phases et {$summary['startup_deliverables']} livrables.")
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        $firstCohort = Cohort::query()->latest()->first();
        if ($firstCohort) {
            $this->selectedCohortId = (string) $firstCohort->id;
        }
    }

    public function getCohortsProperty()
    {
        return Cohort::query()->with('program')->latest()->get();
    }

    public function getSelectedCohortProperty(): ?Cohort
    {
        if (! $this->selectedCohortId) {
            return null;
        }

        return Cohort::query()->with(['program', 'phases', 'enrollments.project.entrepreneurs'])->find($this->selectedCohortId);
    }

    /**
     * Calcule les KPIs globaux consolidés (identiques à l'onglet "Synthèse" et "Récap mensuelle" du classeur Excel SAE-Acte)
     */
    public function getKpisProperty(): array
    {
        $cohort = $this->selectedCohort;

        // Projets / Startups enrôlés
        $projectIds = $cohort
            ? $cohort->enrollments()->pluck('project_id')->filter()->unique()->toArray()
            : Project::pluck('id')->toArray();

        $startupsCount = count($projectIds);

        // 1. Livrables Programme (SAEI)
        $progDefQuery = DeliverableDefinition::where('target_type', DeliverableTarget::PROGRAMME->value);
        if ($cohort && $cohort->program_id) {
            $progDefQuery->where(function ($q) use ($cohort) {
                $q->where('program_id', $cohort->program_id)->orWhereNull('program_id');
            });
        }
        $totalProgDefs = max($progDefQuery->count(), 1);

        $progSubsQuery = DeliverableSubmission::whereHas('definition', function ($q) {
            $q->where('target_type', DeliverableTarget::PROGRAMME->value);
        })->whereIn('status', [DeliverableSubmissionStatus::VALIDE->value, DeliverableSubmissionStatus::VALIDEE->value]);

        if ($cohort) {
            $progSubsQuery->where(function ($q) use ($cohort) {
                $q->where('cohort_id', $cohort->id)->orWhereNull('cohort_id');
            });
        }
        $validesProg = $progSubsQuery->distinct('definition_id')->count('definition_id');
        $avancementProgramme = round(($validesProg / $totalProgDefs) * 100, 1);

        // 2. Livrables Startups
        $startupDefsCount = DeliverableDefinition::where('target_type', DeliverableTarget::STARTUP->value)->count();
        $totalExpectedStartupDeliverables = max($startupDefsCount * $startupsCount, 1);

        $startupSubsValides = DeliverableSubmission::whereIn('project_id', $projectIds)
            ->whereIn('status', [DeliverableSubmissionStatus::VALIDE->value, DeliverableSubmissionStatus::VALIDEE->value])
            ->count();
        $avancementStartups = round(($startupSubsValides / $totalExpectedStartupDeliverables) * 100, 1);

        // 3. Formations collectives & Présences (6 créneaux prévus : J1-J3 matin/après-midi)
        $collectiveSessionsQuery = CoachingSession::where(function ($q) use ($cohort, $projectIds) {
            if ($cohort) {
                $q->where('cohort_id', $cohort->id);
            }
            if (! empty($projectIds)) {
                $q->orWhereIn('project_id', $projectIds);
            }
        })->whereIn('type', ['COLLECTIF', 'ATELIER', 'FORMATION']);

        $collectiveCount = $collectiveSessionsQuery->count();
        $collectiveDone = (clone $collectiveSessionsQuery)->where('status', CoachingSessionStatus::REALISEE->value)->count();
        $tauxPresenceFormation = $collectiveCount > 0 ? round(($collectiveDone / $collectiveCount) * 100, 1) : 100.0;

        // 4. Coaching individuel (8 séances C1 + 8 séances C2 = 16 séances / startup)
        $coachingSessionsQuery = CoachingSession::whereIn('project_id', $projectIds)
            ->whereIn('type', ['INDIVIDUEL', 'MENTORAT', 'SUIVI']);

        $coachingTotalPlanned = max($coachingSessionsQuery->count(), $startupsCount * 16, 1);
        $coachingCompleted = (clone $coachingSessionsQuery)->where('status', CoachingSessionStatus::REALISEE->value)->count();
        $tauxPresenceCoaching = round(($coachingCompleted / $coachingTotalPlanned) * 100, 1);

        // 5. Volume d'heures de coaching effectives
        $totalCoachingHours = (float) (clone $coachingSessionsQuery)
            ->where('status', CoachingSessionStatus::REALISEE->value)
            ->sum('hours_delivered');

        if ($totalCoachingHours <= 0) {
            $totalMinutes = (clone $coachingSessionsQuery)
                ->where('status', CoachingSessionStatus::REALISEE->value)
                ->sum('duration');
            $totalCoachingHours = round($totalMinutes / 60, 1);
        }

        // 6. Retards et alertes
        $lateSubmissions = DeliverableSubmission::whereIn('project_id', $projectIds)
            ->where('is_late', true)
            ->count();

        // Startups avec présence < 75%
        $startupsWithAlerts = 0;
        foreach ($projectIds as $pId) {
            $projSessionsTotal = CoachingSession::where('project_id', $pId)->count();
            $projSessionsDone = CoachingSession::where('project_id', $pId)->where('status', CoachingSessionStatus::REALISEE->value)->count();
            $rate = $projSessionsTotal > 0 ? ($projSessionsDone / $projSessionsTotal) * 100 : 100;
            if ($rate < 75) {
                $startupsWithAlerts++;
            }
        }

        return [
            'startups_count' => $startupsCount,
            'avancement_programme' => min($avancementProgramme, 100),
            'livrables_programme_valides' => $validesProg,
            'livrables_programme_total' => $totalProgDefs,
            'avancement_startups' => min($avancementStartups, 100),
            'livrables_startups_valides' => $startupSubsValides,
            'livrables_startups_total' => $totalExpectedStartupDeliverables,
            'taux_presence_formation' => $tauxPresenceFormation,
            'taux_presence_coaching' => $tauxPresenceCoaching,
            'heures_coaching_total' => $totalCoachingHours,
            'alertes_presence_count' => $startupsWithAlerts,
            'soumissions_en_retard' => $lateSubmissions,
        ];
    }

    /**
     * Synthèse détaillée par Startup (Tableau matrice du suivi individuel)
     */
    public function getStartupsMatrixProperty(): array
    {
        $cohort = $this->selectedCohort;
        if (! $cohort) {
            return [];
        }

        $enrollments = Enrollment::where('cohort_id', $cohort->id)
            ->with(['project.entrepreneurs', 'project.coachAssignments.coach', 'project.deliverableSubmissions.definition'])
            ->get();

        $rows = [];
        $totalExpectedDefs = DeliverableDefinition::where('target_type', DeliverableTarget::STARTUP->value)->count();
        if ($totalExpectedDefs === 0) {
            $totalExpectedDefs = 20; // Référence canevas Startup Loop
        }

        foreach ($enrollments as $enrollment) {
            $project = $enrollment->project;
            if (! $project) {
                continue;
            }

            $entrepreneur = $project->entrepreneurs->first();
            $coach = $project->coachAssignments->where('status', 'ACTIF')->first()?->coach;

            // Sessions de coaching individuelles
            $sessions = CoachingSession::where('project_id', $project->id)->get();
            $c1Sessions = $sessions->filter(fn ($s) => $s->session_number <= 8 || ($s->cohortPhase && str_contains(strtolower($s->cohortPhase->name), 'coaching 1')));
            $c2Sessions = $sessions->filter(fn ($s) => $s->session_number > 8 || ($s->cohortPhase && str_contains(strtolower($s->cohortPhase->name), 'coaching 2')));

            $c1Done = $c1Sessions->where('status', CoachingSessionStatus::REALISEE)->count();
            $c2Done = $c2Sessions->where('status', CoachingSessionStatus::REALISEE)->count();
            $coachingSessionsDone = $sessions->where('status', CoachingSessionStatus::REALISEE)->count();
            $coachingSessionsTotal = max($sessions->count(), 16);
            $tauxCoaching = round(($coachingSessionsDone / $coachingSessionsTotal) * 100);

            // Heures délivrées
            $hours = (float) $sessions->where('status', CoachingSessionStatus::REALISEE)->sum('hours_delivered');
            if ($hours <= 0) {
                $hours = round($sessions->where('status', CoachingSessionStatus::REALISEE)->sum('duration') / 60, 1);
            }

            // Livrables validés
            $submissions = $project->deliverableSubmissions;
            $valides = $submissions->whereIn('status', [DeliverableSubmissionStatus::VALIDE, DeliverableSubmissionStatus::VALIDEE])->count();
            $enRetard = $submissions->where('is_late', true)->count();
            $tauxLivrables = round(($valides / $totalExpectedDefs) * 100);

            // Détection du statut d'alerte (selon règle Afric'innov : Alerte si présence < 75%)
            $status = 'BONNE_VOIE';
            $alertReason = null;

            if ($tauxCoaching < 75 && $sessions->count() >= 3) {
                $status = 'ALERTE_PRESENCE';
                $alertReason = "Présence coaching faible ({$tauxCoaching}% < 75%) - Risque de décrochage";
            } elseif ($enRetard > 0) {
                $status = 'RETARD';
                $alertReason = "{$enRetard} livrable(s) déposé(s) en retard";
            } elseif ($tauxCoaching < 85) {
                $status = 'VIGILANCE';
                $alertReason = "Présence moyenne ({$tauxCoaching}%)";
            }

            $rows[] = [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'sector' => $project->sector ?? 'Général',
                'entrepreneur_name' => $entrepreneur ? "{$entrepreneur->first_name} {$entrepreneur->last_name}" : 'Non renseigné',
                'entrepreneur_phone' => $entrepreneur?->phone,
                'coach_name' => $coach?->name ?? 'Non assigné',
                'c1_done' => $c1Done,
                'c2_done' => $c2Done,
                'coaching_sessions_done' => $coachingSessionsDone,
                'coaching_presence_rate' => $tauxCoaching,
                'coaching_hours' => $hours,
                'livrables_valides' => $valides,
                'livrables_total' => $totalExpectedDefs,
                'livrables_taux' => $tauxLivrables,
                'retards_count' => $enRetard,
                'status' => $status,
                'alert_reason' => $alertReason,
            ];
        }

        return $rows;
    }

    /**
     * Suivi des 42 Livrables SAEI / Programme (conforme à l'onglet 'Suivi Programme')
     */
    public function getProgrammeDeliverablesProperty(): array
    {
        $cohort = $this->selectedCohort;

        $definitions = DeliverableDefinition::where('target_type', DeliverableTarget::PROGRAMME->value)
            ->with(['phase', 'submissions' => function ($q) use ($cohort) {
                if ($cohort) {
                    $q->where('cohort_id', $cohort->id);
                }
            }])
            ->orderBy('sequence_order')
            ->orderBy('id')
            ->get();

        return $definitions->map(function ($def) {
            $latestSub = $def->submissions->first();

            return [
                'id' => $def->id,
                'order' => $def->sequence_order ?? '—',
                'name' => $def->name,
                'phase' => $def->phase?->name ?? 'Transversal',
                'role' => $def->responsible_role?->getLabel() ?? 'SAEI',
                'deadline' => $def->deadline?->format('d/m/Y') ?? 'À définir',
                'submission_id' => $latestSub?->id,
                'submitted_at' => $latestSub?->submitted_at?->format('d/m/Y H:i'),
                'is_late' => $latestSub?->is_late ?? false,
                'status' => $latestSub?->status ?? null,
                'file_path' => $latestSub?->file_path,
            ];
        })->toArray();
    }

    /**
     * Suivi de la progression le long des 6 phases méthodologiques
     */
    public function getPhasesProgressProperty(): array
    {
        $cohort = $this->selectedCohort;

        $standardPhases = [
            1 => [
                'name' => 'Phase 1 : Préparation & Sélection',
                'description' => 'Diagnostic Canvas 6 dimensions, Fiche de sélection, Convention d\'accompagnement',
                'weeks' => 'S1 - S4',
                'color' => 'stone',
            ],
            2 => [
                'name' => 'Phase 2 : Formation 1 (PSF)',
                'description' => 'Bootcamp 3 jours (6 créneaux). Problem statement, Persona, BMC, Plan MVP',
                'weeks' => 'S5 - S6',
                'color' => 'blue',
            ],
            3 => [
                'name' => 'Phase 3 : Coaching 1 PSF Terrain',
                'description' => '8 séances individuelles de coaching. Canevas 24h avant, CR 48h, validation 10 testeurs MVP',
                'weeks' => 'S7 - S14',
                'color' => 'amber',
            ],
            4 => [
                'name' => 'Phase 4 : Formation 2 (PMF)',
                'description' => 'Bootcamp 3 jours. Acquisition 100 utilisateurs, Mesure traction, Modèle financier',
                'weeks' => 'S15 - S16',
                'color' => 'indigo',
            ],
            5 => [
                'name' => 'Phase 5 : Coaching 2 PMF Terrain',
                'description' => '8 séances individuelles. Suivi traction hebdo, Pitch deck investisseurs, Bilan PMF',
                'weeks' => 'S17 - S24',
                'color' => 'purple',
            ],
            6 => [
                'name' => 'Phase 6 : Certification & Clôture',
                'description' => 'Auto-évaluation finale, Jury Afric\'innov (seuils 70/84/85%), Suivi post-programme 6 mois',
                'weeks' => 'S25 - S26',
                'color' => 'emerald',
            ],
        ];

        $results = [];

        foreach ($standardPhases as $idx => $meta) {
            // Cherche la phase correspondante dans la base si configurée
            $dbPhase = $cohort?->phases?->first(fn ($p) => str_contains(strtolower($p->name), "phase {$idx}") || str_contains(strtolower($p->name), strtolower(substr($meta['name'], 10))));

            $startsOn = $dbPhase?->starts_on?->format('d/m/Y') ?? 'Planifié';
            $endsOn = $dbPhase?->ends_on?->format('d/m/Y') ?? 'Planifié';
            $status = $dbPhase?->status ?? ($idx === 1 ? 'EN_COURS' : 'A_VENIR');

            // Calcul du taux d'achèvement approximatif pour la phase
            $phaseDefs = DeliverableDefinition::where('program_phase_id', $dbPhase?->program_phase_id)->count();
            $results[] = [
                'index' => $idx,
                'name' => $meta['name'],
                'description' => $meta['description'],
                'weeks' => $meta['weeks'],
                'color' => $meta['color'],
                'starts_on' => $startsOn,
                'ends_on' => $endsOn,
                'status' => $status,
                'deliverables_count' => $phaseDefs > 0 ? $phaseDefs : ($idx % 2 === 0 ? 4 : 8),
            ];
        }

        return $results;
    }

    /**
     * Alertes M&E actives nécessitant une action du coordinateur SAEI
     */
    public function getAlertsProperty(): array
    {
        $matrix = $this->startupsMatrix;
        $alerts = [];

        foreach ($matrix as $startup) {
            if ($startup['status'] === 'ALERTE_PRESENCE') {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => "Risque d'abandon (< 75% présence) : {$startup['project_name']}",
                    'message' => "Le taux d'assiduité aux séances de coaching est de {$startup['coaching_presence_rate']}%. Le fondateur ({$startup['entrepreneur_name']}) doit être contacté.",
                    'project_id' => $startup['project_id'],
                    'phone' => $startup['entrepreneur_phone'],
                ];
            } elseif ($startup['status'] === 'RETARD') {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => "Retard sur les livrables : {$startup['project_name']}",
                    'message' => "{$startup['retards_count']} livrable(s) soumis hors délai. Rappeler les échéances au coach {$startup['coach_name']}.",
                    'project_id' => $startup['project_id'],
                    'phone' => $startup['entrepreneur_phone'],
                ];
            }
        }

        return $alerts;
    }

    /**
     * Exporte la synthèse M&E sous forme de fichier CSV compatible Excel
     */
    public function exportCsv(): StreamedResponse
    {
        $cohort = $this->selectedCohort;
        $matrix = $this->startupsMatrix;
        $filename = 'suivi_sae_acte_'.($cohort?->slug ?? 'cohorte').'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($matrix) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Startup / Projet',
                'Secteur',
                'Entrepreneur / Fondateur',
                'Coach Référent',
                'Séances C1 Réalisées',
                'Séances C2 Réalisées',
                'Total Séances Réalisées',
                'Taux Présence Coaching (%)',
                'Heures de Coaching (h)',
                'Livrables Validés',
                'Total Livrables Attendus',
                'Taux Livrables (%)',
                'Retards Détectés',
                'Statut M&E',
                'Remarque / Alerte',
            ]);

            foreach ($matrix as $row) {
                fputcsv($handle, [
                    $row['project_name'],
                    $row['sector'],
                    $row['entrepreneur_name'],
                    $row['coach_name'],
                    $row['c1_done'],
                    $row['c2_done'],
                    $row['coaching_sessions_done'],
                    $row['coaching_presence_rate'],
                    $row['coaching_hours'],
                    $row['livrables_valides'],
                    $row['livrables_total'],
                    $row['livrables_taux'],
                    $row['retards_count'],
                    $row['status'],
                    $row['alert_reason'] ?? 'Normal',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
