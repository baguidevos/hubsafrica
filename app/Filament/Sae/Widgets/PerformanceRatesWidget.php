<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachingSessionStatus;
use App\Enums\CoachStatus;
use App\Enums\CohortStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\DeliverableTarget;
use App\Enums\FundingStatus;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\FundingRecord;
use App\Models\Project;
use Filament\Widgets\Widget;

class PerformanceRatesWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.sae.widgets.performance-rates-widget';

    public function getFormulesData(): array
    {
        // 1. Taux d'admission (décisions rendues hors brouillons et retraits)
        $examinees = Application::whereNotNull('reviewed_at')->count();
        if ($examinees === 0) {
            $examinees = Application::whereIn('status', [
                ApplicationStatus::ACCEPTEE->value,
                ApplicationStatus::REFUSEE->value,
                ApplicationStatus::EN_ATTENTE->value,
                ApplicationStatus::REORIENTEE->value,
            ])->count();
        }
        $acceptees = Application::where('status', ApplicationStatus::ACCEPTEE->value)->count();
        $tauxAdmission = $examinees > 0 ? min(100, round(($acceptees / $examinees) * 100, 1)) : 0;

        // 2. Taux de remplissage des cohortes actives (OUVERTE | EN_COURS)
        $activeCohortIds = Cohort::whereIn('status', [
            CohortStatus::OUVERTE->value,
            CohortStatus::EN_COURS->value,
        ])->pluck('id');
        $capacite = (int) Cohort::whereIn('id', $activeCohortIds)->sum('capacity');
        $integres = $activeCohortIds->isNotEmpty()
            ? Enrollment::whereIn('cohort_id', $activeCohortIds)->where('status', 'ACTIF')->count()
            : 0;
        $tauxRemplissage = $capacite > 0 ? min(100, round(($integres / $capacite) * 100, 1)) : 0;

        // 3. Charge par coach
        $coachsActifs = Coach::whereIn('status', [CoachStatus::ACTIF->value, CoachStatus::ACTIVE->value])->count();
        $accompagnements = CoachAssignment::where('status', CoachAssignmentStatus::ACTIF->value)->count();
        $chargeCoach = $coachsActifs > 0 ? round($accompagnements / $coachsActifs, 1) : 0;
        $chargeTone = $coachsActifs === 0
            ? 'rose'
            : ($chargeCoach <= 5 ? 'emerald' : ($chargeCoach <= 8 ? 'amber' : 'rose'));

        // 4. Réalisation des séances de coaching (hors annulées)
        $seancesPrevues = CoachingSession::where('status', '!=', CoachingSessionStatus::ANNULEE->value)->count();
        $seancesRealisees = CoachingSession::where('status', CoachingSessionStatus::REALISEE->value)->count();
        $tauxRealisationSeances = $seancesPrevues > 0 ? round(($seancesRealisees / $seancesPrevues) * 100, 1) : 0;

        // 5. Taux de dépôt des livrables (sur projets actifs)
        $projetsActifs = Project::where('status', ProjectStatus::ACTIF->value)->count();
        $depots = DeliverableSubmission::where('status', '!=', DeliverableSubmissionStatus::BROUILLON->value)
            ->whereHas('project', fn ($q) => $q->where('status', ProjectStatus::ACTIF->value))
            ->count();
        $startupDefsCount = DeliverableDefinition::where('target_type', DeliverableTarget::STARTUP->value)->count();
        $attendus = $startupDefsCount > 0 ? ($projetsActifs * $startupDefsCount) : 0;
        $tauxDepot = $attendus > 0 ? min(100, round(($depots / $attendus) * 100, 1)) : 0;

        // 6. Taux de validation des livrables
        $evalues = DeliverableSubmission::whereIn('status', [
            DeliverableSubmissionStatus::VALIDEE->value,
            DeliverableSubmissionStatus::VALIDE->value,
            DeliverableSubmissionStatus::REJETEE->value,
            DeliverableSubmissionStatus::NON_VALIDE->value,
            DeliverableSubmissionStatus::CORRECTION_DEMANDEE->value,
        ])->count();
        $valides = DeliverableSubmission::whereIn('status', [
            DeliverableSubmissionStatus::VALIDEE->value,
            DeliverableSubmissionStatus::VALIDE->value,
        ])->count();
        $tauxValidation = $evalues > 0 ? round(($valides / $evalues) * 100, 1) : 0;

        // 7. Couverture des financements (obtenus / recherchés)
        $besoinsFinancement = (float) FundingRecord::sum('amount_sought');
        $obtenusFinancement = (float) FundingRecord::whereIn('status', [
            FundingStatus::ACCORDE->value,
            FundingStatus::DECAISSE->value,
            FundingStatus::INVESTI->value,
        ])->sum('amount_obtained');
        $tauxCouvertureFinancement = $besoinsFinancement > 0
            ? min(100, round(($obtenusFinancement / $besoinsFinancement) * 100, 1))
            : 0;

        return [
            [
                'key' => 'admission',
                'label' => 'Taux d\'admission',
                'val' => $tauxAdmission.' %',
                'raw' => $tauxAdmission,
                'tone' => $this->rateTone($tauxAdmission),
                'num' => "Candidatures acceptées ({$acceptees})",
                'den' => "Décisions rendues ({$examinees}) × 100",
            ],
            [
                'key' => 'remplissage',
                'label' => 'Remplissage cohortes',
                'val' => $tauxRemplissage.' %',
                'raw' => $tauxRemplissage,
                'tone' => $this->rateTone($tauxRemplissage),
                'num' => "Enrôlés actifs ({$integres})",
                'den' => $capacite > 0 ? "Capacité ouverte/en cours ({$capacite}) × 100" : 'Capacité non définie (0)',
            ],
            [
                'key' => 'chargeCoach',
                'label' => 'Charge par coach',
                'val' => $chargeCoach.' proj.',
                'raw' => $chargeCoach,
                'tone' => $chargeTone,
                'num' => "Accompagnements ({$accompagnements})",
                'den' => "Coachs actifs ({$coachsActifs})",
            ],
            [
                'key' => 'seances',
                'label' => 'Réalisation séances',
                'val' => $tauxRealisationSeances.' %',
                'raw' => $tauxRealisationSeances,
                'tone' => $this->rateTone($tauxRealisationSeances),
                'num' => "Séances réalisées ({$seancesRealisees})",
                'den' => "Séances planifiées ({$seancesPrevues}) × 100",
            ],
            [
                'key' => 'depot',
                'label' => 'Taux de dépôt',
                'val' => $tauxDepot.' %',
                'raw' => $tauxDepot,
                'tone' => $this->rateTone($tauxDepot),
                'num' => "Livrables déposés ({$depots})",
                'den' => "Livrables attendus ({$attendus}) × 100",
            ],
            [
                'key' => 'validation',
                'label' => 'Taux de validation',
                'val' => $tauxValidation.' %',
                'raw' => $tauxValidation,
                'tone' => $this->rateTone($tauxValidation),
                'num' => "Livrables validés ({$valides})",
                'den' => "Livrables évalués ({$evalues}) × 100",
            ],
            [
                'key' => 'financement',
                'label' => 'Couverture financement',
                'val' => $tauxCouvertureFinancement.' %',
                'raw' => $tauxCouvertureFinancement,
                'tone' => $this->rateTone($tauxCouvertureFinancement),
                'num' => 'Obtenus ('.number_format($obtenusFinancement, 0, ',', ' ').')',
                'den' => $besoinsFinancement > 0
                    ? 'Recherchés ('.number_format($besoinsFinancement, 0, ',', ' ').') × 100'
                    : 'Besoins non renseignés (0)',
            ],
        ];
    }

    private function rateTone(float $rate): string
    {
        if ($rate >= 60) {
            return 'emerald';
        }

        if ($rate >= 30) {
            return 'amber';
        }

        return 'rose';
    }
}
