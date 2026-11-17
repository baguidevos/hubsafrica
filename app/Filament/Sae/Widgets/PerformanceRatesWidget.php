<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachStatus;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\DeliverableTarget;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use Filament\Widgets\Widget;

class PerformanceRatesWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.sae.widgets.performance-rates-widget';

    public function getFormulesData(): array
    {
        // 1. Taux d'admission
        $examinees = Application::whereNotNull('reviewed_at')->count();
        if ($examinees === 0) {
            $examinees = Application::whereNotIn('status', [
                ApplicationStatus::BROUILLON->value,
                ApplicationStatus::RETIREE->value,
            ])->count();
        }
        $acceptees = Application::where('status', ApplicationStatus::ACCEPTEE->value)->count();
        $tauxAdmission = $examinees > 0 ? round(($acceptees / $examinees) * 100, 1) : 0;

        // 2. Taux de remplissage
        $integres = Enrollment::count();
        $capacite = (int) Cohort::sum('capacity');
        $tauxRemplissage = $capacite > 0 ? min(100, round(($integres / $capacite) * 100, 1)) : 0;

        // 3. Charge par coach
        $coachsActifs = Coach::whereIn('status', [CoachStatus::ACTIF->value, CoachStatus::ACTIVE->value])->count();
        $accompagnements = CoachAssignment::where('status', CoachAssignmentStatus::ACTIF->value)->count();
        $chargeCoach = $coachsActifs > 0 ? round($accompagnements / $coachsActifs, 1) : 0;
        $chargeTone = $coachsActifs === 0
            ? 'rose'
            : ($chargeCoach <= 5 ? 'emerald' : ($chargeCoach <= 8 ? 'amber' : 'rose'));

        // 4. Taux de dépôt
        $projetsActifs = Project::where('status', ProjectStatus::ACTIF->value)->count();
        $depots = DeliverableSubmission::where('status', '!=', DeliverableSubmissionStatus::BROUILLON->value)->count();
        $startupDefsCount = DeliverableDefinition::where('target_type', DeliverableTarget::STARTUP->value)->count();
        $attendus = $startupDefsCount > 0 ? ($projetsActifs * $startupDefsCount) : 0;
        $tauxDepot = $attendus > 0 ? min(100, round(($depots / $attendus) * 100, 1)) : 0;

        // 5. Taux de validation
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

        // 6. Taux de resoumission
        $retours = DeliverableSubmission::whereIn('status', [
            DeliverableSubmissionStatus::REJETEE->value,
            DeliverableSubmissionStatus::NON_VALIDE->value,
            DeliverableSubmissionStatus::CORRECTION_DEMANDEE->value,
        ])->count();
        $resoumis = DeliverableSubmission::where('version', '>', 1)->count();
        $tauxResoumission = $retours > 0 ? min(100, round(($resoumis / $retours) * 100, 1)) : 0;

        // 7. Taux de progression
        $eligibles = $projetsActifs;
        $promus = ProjectLevelHistory::whereHas('project', function ($q) {
            $q->where('status', ProjectStatus::ACTIF->value);
        })->distinct('project_id')->count('project_id');
        $tauxProgression = $eligibles > 0 ? min(100, round(($promus / $eligibles) * 100, 1)) : 0;

        return [
            [
                'key' => 'admission',
                'label' => 'Taux d\'admission',
                'val' => $tauxAdmission.' %',
                'raw' => $tauxAdmission,
                'tone' => $this->rateTone($tauxAdmission),
                'num' => "Candidatures acceptées ({$acceptees})",
                'den' => "Candidatures examinées ({$examinees}) × 100",
            ],
            [
                'key' => 'remplissage',
                'label' => 'Taux de remplissage',
                'val' => $tauxRemplissage.' %',
                'raw' => $tauxRemplissage,
                'tone' => $this->rateTone($tauxRemplissage),
                'num' => "Effectif intégré ({$integres})",
                'den' => $capacite > 0 ? "Capacité prévue ({$capacite}) × 100" : 'Capacité non définie (0)',
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
                'key' => 'resoumission',
                'label' => 'Taux de resoumission',
                'val' => $tauxResoumission.' %',
                'raw' => $tauxResoumission,
                'tone' => $this->rateTone($tauxResoumission),
                'num' => "Livrables redéposés ({$resoumis})",
                'den' => "Livrables rejetés ({$retours}) × 100",
            ],
            [
                'key' => 'progression',
                'label' => 'Taux de progression',
                'val' => $tauxProgression.' %',
                'raw' => $tauxProgression,
                'tone' => $this->rateTone($tauxProgression),
                'num' => "Projets promus ({$promus})",
                'den' => "Projets actifs ({$eligibles}) × 100",
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
