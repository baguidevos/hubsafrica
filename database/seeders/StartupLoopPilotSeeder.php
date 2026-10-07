<?php

namespace Database\Seeders;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Enums\DeliverableResponsibleRole;
use App\Enums\DeliverableTarget;
use App\Enums\SessionPeriodOfDay;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\Program;
use App\Models\ProgramPhase;
use App\Models\Project;
use App\Models\Sae;
use Illuminate\Database\Seeder;

class StartupLoopPilotSeeder extends Seeder
{
    public function run(): void
    {
        $sae = Sae::first();
        if (! $sae) {
            return;
        }

        $program = Program::where('sae_id', $sae->id)->first() ?? Program::first();
        $cohort = Cohort::where('sae_id', $sae->id)->first() ?? Cohort::first();

        // 1. Les 6 Phases Méthodologiques Startup Loop (Afric'innov / GIZ Pilot)
        $phasesData = [
            [
                'position' => 1,
                'name' => 'Phase 1 : Préparation & Sélection',
                'description' => 'Diagnostic Canvas 6 dimensions, Fiche de sélection, convention d\'incubation',
                'objectives' => 'Sélectionner 10 startups à fort potentiel et établir le diagnostic de maturité initial.',
                'passing_criteria' => 'Diagnostic 6D complété et convention signée.',
                'duration_weeks' => 4,
            ],
            [
                'position' => 2,
                'name' => 'Phase 2 : Formation 1 (PSF)',
                'description' => 'Bootcamp intensif 3 jours (6 créneaux émargés). Méthodologie Problem-Solution Fit.',
                'objectives' => 'Valider le problème, formaliser le persona, le BMC initial et le plan de test MVP.',
                'passing_criteria' => 'Assiduité 100% aux 6 créneaux et validation du plan MVP.',
                'duration_weeks' => 2,
            ],
            [
                'position' => 3,
                'name' => 'Phase 3 : Coaching 1 PSF Terrain',
                'description' => '8 séances individuelles de coaching bimensuel. Canevas préparatoire avant séance, rapport 48h.',
                'objectives' => 'Tester le MVP auprès d\'au moins 10 utilisateurs et pivoter si nécessaire.',
                'passing_criteria' => 'Minimum 75% de présence aux 8 séances et rapport test 10 utilisateurs validé.',
                'duration_weeks' => 8,
            ],
            [
                'position' => 4,
                'name' => 'Phase 4 : Formation 2 (PMF)',
                'description' => 'Bootcamp 3 jours Product-Market Fit : acquisition, métriques de traction, tableau de bord.',
                'objectives' => 'Structurer la stratégie d\'acquisition de 100 premiers utilisateurs et le modèle économique.',
                'passing_criteria' => 'Assiduité aux formations et livraison du tableau de bord de traction.',
                'duration_weeks' => 2,
            ],
            [
                'position' => 5,
                'name' => 'Phase 5 : Coaching 2 PMF Terrain',
                'description' => '8 séances individuelles de coaching de traction commerciale et investment readiness.',
                'objectives' => 'Atteindre les métriques de traction, formaliser le pitch deck et le bilan PMF.',
                'passing_criteria' => 'Présence aux 8 séances et validation du pitch deck final.',
                'duration_weeks' => 8,
            ],
            [
                'position' => 6,
                'name' => 'Phase 6 : Certification & Clôture',
                'description' => 'Auto-évaluation sur les 5 axes, délibération du jury Afric\'innov et plan post-programme.',
                'objectives' => 'Franchir les seuils de passage (70/84/85%) et obtenir la certification.',
                'passing_criteria' => 'Note globale >= 70% et quitus de la SAEI.',
                'duration_weeks' => 2,
            ],
        ];

        $createdPhases = [];
        if ($program) {
            foreach ($phasesData as $pData) {
                $createdPhases[$pData['position']] = ProgramPhase::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'position' => $pData['position'],
                    ],
                    $pData
                );
            }
        }

        if ($cohort) {
            $cohort->syncPhasesFromProgram();
        }

        // 2. Livrables Types Startups (issus de l'onglet 'Templates livrable Startups')
        $startupDeliverables = [
            ['seq' => 1, 'phase' => 1, 'name' => 'Diagnostic Initial - Auto-évaluation 6 dimensions', 'desc' => 'Grille d\'analyse de maturité préalable'],
            ['seq' => 2, 'phase' => 1, 'name' => 'Fiche d\'engagement & objectifs d\'accompagnement', 'desc' => 'Contrat moral et KPI individuels'],
            ['seq' => 3, 'phase' => 2, 'name' => 'Problem Statement & Analyse de marché', 'desc' => 'Définition précise du problème adressé'],
            ['seq' => 4, 'phase' => 2, 'name' => 'Persona & Empathy Map validés', 'desc' => 'Caractérisation détaillée de la cible'],
            ['seq' => 5, 'phase' => 2, 'name' => 'Business Model Canvas Initial (BMC)', 'desc' => 'Hypothèses de modèle économique'],
            ['seq' => 6, 'phase' => 2, 'name' => 'Plan de test & Spécifications du MVP', 'desc' => 'Définition du prototype minimal viable'],
            ['seq' => 7, 'phase' => 3, 'name' => 'Canevas préalable Coaching 1 (Pré-séance)', 'desc' => 'Préparation de la session de coaching terrain'],
            ['seq' => 8, 'phase' => 3, 'name' => 'Synthèse des retours de 10 testeurs MVP', 'desc' => 'Preuve d\'interaction terrain et feedbacks'],
            ['seq' => 9, 'phase' => 3, 'name' => 'BMC révisé & Validation Problem-Solution Fit', 'desc' => 'Pivot ou confirmation de la solution'],
            ['seq' => 10, 'phase' => 4, 'name' => 'Plan d\'acquisition des 100 premiers clients', 'desc' => 'Stratégie de commercialisation et canaux'],
            ['seq' => 11, 'phase' => 4, 'name' => 'Tableau de bord des métriques de traction', 'desc' => 'Indicateurs CAC, LTV, conversion, churn'],
            ['seq' => 12, 'phase' => 4, 'name' => 'Modèle financier prévisionnel simplifié', 'desc' => 'Plan de trésorerie et hypothèses de revenus'],
            ['seq' => 13, 'phase' => 5, 'name' => 'Canevas préalable Coaching 2 (Pré-séance)', 'desc' => 'Préparation du suivi de traction hebdo'],
            ['seq' => 14, 'phase' => 5, 'name' => 'Rapport hebdomadaire de traction commerciale', 'desc' => 'Chiffres de ventes ou d\'utilisateurs actifs'],
            ['seq' => 15, 'phase' => 5, 'name' => 'Pitch Deck Investment Readiness', 'desc' => 'Présentation de 10-12 slides pour investisseurs'],
            ['seq' => 16, 'phase' => 5, 'name' => 'Bilan Product-Market Fit (PMF)', 'desc' => 'Validation de l\'adéquation produit-marché'],
            ['seq' => 17, 'phase' => 6, 'name' => 'Auto-évaluation finale de sortie (5 axes)', 'desc' => 'Mesure des progrès accomplis sur les seuils'],
            ['seq' => 18, 'phase' => 6, 'name' => 'Plan d\'action post-accompagnement à 6 mois', 'desc' => 'Feuille de route d\'autonomie et croissance'],
        ];

        foreach ($startupDeliverables as $item) {
            DeliverableDefinition::updateOrCreate(
                [
                    'sae_id' => $sae->id,
                    'name' => $item['name'],
                    'target_type' => DeliverableTarget::STARTUP->value,
                ],
                [
                    'program_id' => $program?->id,
                    'program_phase_id' => $createdPhases[$item['phase']]->id ?? null,
                    'responsible_role' => DeliverableResponsibleRole::FONDATEUR->value,
                    'sequence_order' => $item['seq'],
                    'description' => $item['desc'],
                    'is_required' => true,
                    'max_score' => 100,
                    'passing_score' => 70,
                ]
            );
        }

        // 3. Livrables Types SAEI / Programme (issus de 'Templates livrable SAEI & Coach' et chronogramme)
        $progDeliverables = [
            ['seq' => 1, 'phase' => 1, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Fiches de sélection et procès-verbal du comité'],
            ['seq' => 2, 'phase' => 1, 'role' => DeliverableResponsibleRole::SAEI, 'name' => 'Conventions d\'incubation signées (Lot complet)'],
            ['seq' => 3, 'phase' => 1, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport consolidé du diagnostic initial des 10 startups'],
            ['seq' => 4, 'phase' => 2, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Feuilles d\'émargement Formation 1 (J1 à J3 - 6 créneaux)'],
            ['seq' => 5, 'phase' => 2, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Rapport pédagogique & évaluations de la Formation 1'],
            ['seq' => 6, 'phase' => 3, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Fiches de cadrage des 8 séances de Coaching 1'],
            ['seq' => 7, 'phase' => 3, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Comptes-rendus des 8 séances de coaching individuel (Lot C1)'],
            ['seq' => 8, 'phase' => 3, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Bilan M&E de mi-parcours & validation Problem-Solution Fit'],
            ['seq' => 9, 'phase' => 4, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Feuilles d\'émargement Formation 2 (J1 à J3 - 6 créneaux)'],
            ['seq' => 10, 'phase' => 4, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Rapport pédagogique & évaluations de la Formation 2'],
            ['seq' => 11, 'phase' => 5, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Comptes-rendus des 8 séances de coaching individuel (Lot C2)'],
            ['seq' => 12, 'phase' => 5, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport mensuel de traction et suivi des heures de coaching'],
            ['seq' => 13, 'phase' => 6, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Procès-verbal de délibération finale & Attestations'],
            ['seq' => 14, 'phase' => 6, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport final de clôture du programme & Synthèse M&E'],
        ];

        foreach ($progDeliverables as $item) {
            DeliverableDefinition::updateOrCreate(
                [
                    'sae_id' => $sae->id,
                    'name' => $item['name'],
                    'target_type' => DeliverableTarget::PROGRAMME->value,
                ],
                [
                    'program_id' => $program?->id,
                    'program_phase_id' => $createdPhases[$item['phase']]->id ?? null,
                    'responsible_role' => $item['role']->value,
                    'sequence_order' => $item['seq'],
                    'is_required' => true,
                    'max_score' => 100,
                    'passing_score' => 70,
                ]
            );
        }

        // 4. Mettre à jour les sessions existantes pour illustrer les 8 séances C1 / C2 et heures
        $projects = Project::where('sae_id', $sae->id)->get();
        $coach = Coach::where('sae_id', $sae->id)->first();

        if ($projects->isNotEmpty() && $coach) {
            foreach ($projects->take(3) as $projIndex => $project) {
                for ($sessionNum = 1; $sessionNum <= 4; $sessionNum++) {
                    CoachingSession::firstOrCreate(
                        [
                            'sae_id' => $sae->id,
                            'coach_id' => $coach->id,
                            'project_id' => $project->id,
                            'session_number' => $sessionNum,
                        ],
                        [
                            'date' => now()->subDays((5 - $sessionNum) * 7),
                            'duration' => 90,
                            'hours_delivered' => 1.5,
                            'period_of_day' => SessionPeriodOfDay::MATIN,
                            'type' => CoachingSessionType::INDIVIDUEL,
                            'theme' => "Séance C1.{$sessionNum} : Cadrage PSF et validation terrain",
                            'status' => CoachingSessionStatus::REALISEE,
                            'cohort_id' => $cohort?->id,
                        ]
                    );
                }
            }
        }
    }
}
