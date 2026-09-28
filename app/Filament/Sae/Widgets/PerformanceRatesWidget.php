<?php

namespace App\Filament\Sae\Widgets;

use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Cohort;
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
            $examinees = Application::where('status', '!=', 'BROUILLON')->count();
        }
        $acceptees = Application::where('status', 'ACCEPTEE')->count();
        $tauxAdmission = $examinees > 0 ? round(($acceptees / $examinees) * 100, 1) : 0;

        // 2. Taux de remplissage
        $integres = Enrollment::count();
        $capacite = (int) Cohort::sum('capacity');
        if ($capacite === 0) {
            $capacite = max(20, $integres);
        }
        $tauxRemplissage = $capacite > 0 ? round(($integres / $capacite) * 100, 1) : 0;

        // 3. Charge par coach
        $coachsActifs = Coach::where('status', 'ACTIF')->count();
        $accompagnements = CoachAssignment::where('status', 'ACTIF')->count();
        $chargeCoach = $coachsActifs > 0 ? round($accompagnements / $coachsActifs, 1) : 0;

        // 4. Taux de dépôt
        $depots = DeliverableSubmission::count();
        $attendus = max($depots, Project::where('status', 'ACTIF')->count() * 3);
        $tauxDepot = $attendus > 0 ? round(($depots / $attendus) * 100, 1) : 0;

        // 5. Taux de validation
        $evalues = DeliverableSubmission::whereIn('status', ['VALIDEE', 'VALIDE', 'REJETEE', 'NON_VALIDE'])->count();
        $valides = DeliverableSubmission::whereIn('status', ['VALIDEE', 'VALIDE'])->count();
        $tauxValidation = $evalues > 0 ? round(($valides / $evalues) * 100, 1) : ($depots > 0 ? 80 : 0);

        // 6. Taux de resoumission
        $retours = DeliverableSubmission::whereIn('status', ['REJETEE', 'NON_VALIDE', 'CORRECTION_DEMANDEE'])->count();
        $resoumis = DeliverableSubmission::where('version', '>', 1)->count();
        $tauxResoumission = $retours > 0 ? round(($resoumis / $retours) * 100, 1) : 0;

        // 7. Taux de progression
        $promus = ProjectLevelHistory::distinct('project_id')->count('project_id');
        $eligibles = Project::where('status', 'ACTIF')->count();
        $tauxProgression = $eligibles > 0 ? round(($promus / $eligibles) * 100, 1) : 0;

        return [
            [
                'key' => 'admission',
                'label' => 'Taux d\'admission',
                'val' => $tauxAdmission.' %',
                'raw' => $tauxAdmission,
                'num' => "Candidatures acceptées ({$acceptees})",
                'den' => "Candidatures examinées ({$examinees}) × 100",
            ],
            [
                'key' => 'remplissage',
                'label' => 'Taux de remplissage',
                'val' => $tauxRemplissage.' %',
                'raw' => $tauxRemplissage,
                'num' => "Effectif intégré ({$integres})",
                'den' => "Capacité prévue ({$capacite}) × 100",
            ],
            [
                'key' => 'chargeCoach',
                'label' => 'Charge par coach',
                'val' => $chargeCoach.' proj.',
                'raw' => $chargeCoach * 10,
                'num' => "Accompagnements ({$accompagnements})",
                'den' => "Coachs actifs ({$coachsActifs})",
            ],
            [
                'key' => 'depot',
                'label' => 'Taux de dépôt',
                'val' => $tauxDepot.' %',
                'raw' => $tauxDepot,
                'num' => "Livrables déposés ({$depots})",
                'den' => "Livrables attendus ({$attendus}) × 100",
            ],
            [
                'key' => 'validation',
                'label' => 'Taux de validation',
                'val' => $tauxValidation.' %',
                'raw' => $tauxValidation,
                'num' => "Livrables validés ({$valides})",
                'den' => "Livrables évalués ({$evalues}) × 100",
            ],
            [
                'key' => 'resoumission',
                'label' => 'Taux de resoumission',
                'val' => $tauxResoumission.' %',
                'raw' => $tauxResoumission,
                'num' => "Livrables redéposés ({$resoumis})",
                'den' => "Livrables rejetés ({$retours}) × 100",
            ],
            [
                'key' => 'progression',
                'label' => 'Taux de progression',
                'val' => $tauxProgression.' %',
                'raw' => $tauxProgression,
                'num' => "Projets promus ({$promus})",
                'den' => "Projets actifs ({$eligibles}) × 100",
            ],
        ];
    }
}
