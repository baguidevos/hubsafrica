<?php

namespace Database\Seeders;

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\PartnerType;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use App\Models\Cohort;
use App\Models\EvaluationCriterion;
use App\Models\Indicator;
use App\Models\IndicatorEntry;
use App\Models\Partner;
use App\Models\Program;
use App\Models\ProgramPhase;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccompanimentArchitectureSeeder extends Seeder
{
    public function run(): void
    {
        $sae = Sae::first();
        if (! $sae) {
            return;
        }

        // 1. Partenaires & Bailleurs de fonds
        $partnersData = [
            [
                'name' => 'AFD - Agence Française de Développement',
                'type' => PartnerType::BAILLEUR,
                'website' => 'https://www.afd.fr',
                'contact_name' => 'Jean-Marc Dupont',
                'contact_email' => 'dupont.jm@afd.fr',
                'notes' => 'Financement de programmes d\'innovation durable et entrepreneuriat féminin.',
            ],
            [
                'name' => 'GIZ - Coopération Allemande',
                'type' => PartnerType::BAILLEUR,
                'website' => 'https://www.giz.de',
                'contact_name' => 'Karin Müller',
                'contact_email' => 'karin.mueller@giz.de',
                'notes' => 'Appui aux filières agri-tech et transformation numérique locale.',
            ],
            [
                'name' => 'Orange Digital Center',
                'type' => PartnerType::TECHNIQUE,
                'website' => 'https://www.orangedigitalcenters.com',
                'contact_name' => 'Amadou Ba',
                'contact_email' => 'amadou.ba@orange.com',
                'notes' => 'Soutien cloud, mise à disposition de mentors techniques et locaux d\'incubation.',
            ],
        ];

        $createdPartners = [];
        foreach ($partnersData as $data) {
            $createdPartners[] = Partner::updateOrCreate(
                ['sae_id' => $sae->id, 'name' => $data['name']],
                $data
            );
        }

        // 2. Association des Partenaires aux Programmes & Définition des Phases
        $programs = Program::where('sae_id', $sae->id)->get();

        foreach ($programs as $program) {
            // Rattachement des partenaires
            if (isset($createdPartners[0])) {
                $program->partners()->syncWithoutDetaching([
                    $createdPartners[0]->id => [
                        'sae_id' => $sae->id,
                        'role' => 'Bailleur Principal',
                        'contribution_amount' => 50000000.00,
                        'currency' => 'XOF',
                    ],
                ]);
            }
            if (isset($createdPartners[2])) {
                $program->partners()->syncWithoutDetaching([
                    $createdPartners[2]->id => [
                        'sae_id' => $sae->id,
                        'role' => 'Partenaire Technique & Cloud',
                        'contribution_amount' => 10000000.00,
                        'currency' => 'XOF',
                    ],
                ]);
            }

            // Création des phases templates
            $phases = [
                [
                    'name' => 'Phase 1 : Diagnostic & Boot Camp',
                    'description' => 'Cadrage stratégique, revue de modèle économique et alignement des objectifs.',
                    'objectives' => 'Valider la proposition de valeur et définir la feuille de route opérationnelle.',
                    'passing_criteria' => 'Diagnostic 360 validé par le mentor et présence au boot camp.',
                    'position' => 1,
                    'duration_weeks' => 4,
                ],
                [
                    'name' => 'Phase 2 : Structuration & Prototypage MVP',
                    'description' => 'Développement ou amélioration du produit/service et premiers tests utilisateurs.',
                    'objectives' => 'Mettre en place la première version testée auprès d\'un panel d\'utilisateurs.',
                    'passing_criteria' => 'Preuve de concept fonctionnelle et retours de 10 utilisateurs cibles.',
                    'position' => 2,
                    'duration_weeks' => 8,
                ],
                [
                    'name' => 'Phase 3 : Go-to-Market & Accélération Commerciale',
                    'description' => 'Acquisition clients, structuration des canaux de vente et premiers revenus.',
                    'objectives' => 'Atteindre un flux de facturation récurrent et affiner le coût d\'acquisition.',
                    'passing_criteria' => 'Au moins 3 clients payants ou 100 utilisateurs actifs réguliers.',
                    'position' => 3,
                    'duration_weeks' => 8,
                ],
                [
                    'name' => 'Phase 4 : Demo Day & Préparation Levée de Fonds',
                    'description' => 'Pitch deck investisseur, data room financière et présentation devant le réseau de partenaires.',
                    'objectives' => 'Présenter son projet aux business angels et fonds d\'amorçage.',
                    'passing_criteria' => 'Data room complète et passage devant le jury du Demo Day.',
                    'position' => 4,
                    'duration_weeks' => 4,
                ],
            ];

            foreach ($phases as $phaseData) {
                ProgramPhase::firstOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'position' => $phaseData['position'],
                    ],
                    $phaseData
                );
            }

            // Création des critères d'évaluation pour le jury de sélection
            $criteria = [
                [
                    'name' => 'Pertinence du problème & Proposition de valeur',
                    'description' => 'Clarté du problème adressé et différenciation de la solution proposée.',
                    'weight' => 3,
                    'max_score' => 10,
                    'position' => 1,
                ],
                [
                    'name' => 'Complémentarité et compétence de l\'équipe fondatrice',
                    'description' => 'Expérience, complémentarité technique/business et capacité d\'exécution.',
                    'weight' => 3,
                    'max_score' => 10,
                    'position' => 2,
                ],
                [
                    'name' => 'Traction initiale ou preuve de marché',
                    'description' => 'Premiers utilisateurs, précommandes, partenariats signés ou prototype.',
                    'weight' => 2,
                    'max_score' => 10,
                    'position' => 3,
                ],
                [
                    'name' => 'Potentiel de passage à l\'échelle & impact socio-économique',
                    'description' => 'Marché adressable en Afrique de l\'Ouest et création d\'emplois locaux.',
                    'weight' => 2,
                    'max_score' => 10,
                    'position' => 4,
                ],
            ];

            foreach ($criteria as $critData) {
                EvaluationCriterion::firstOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'name' => $critData['name'],
                    ],
                    $critData
                );
            }
        }

        // 3. Synchronisation des phases sur les cohortes existantes
        $cohorts = Cohort::where('sae_id', $sae->id)->get();
        foreach ($cohorts as $cohort) {
            $cohort->syncPhasesFromProgram();
        }

        // 4. Démonstration de notation jury sur une candidature existante
        $application = Application::where('sae_id', $sae->id)->first();
        $juryUser = User::where('email', 'like', '%@teranga.sn')->first() ?? User::first();

        if ($application && $juryUser) {
            $eval = ApplicationEvaluation::firstOrCreate(
                [
                    'sae_id' => $sae->id,
                    'application_id' => $application->id,
                    'evaluator_id' => $juryUser->id,
                ],
                [
                    'comment' => 'Excellent dossier de candidature, équipe très engagée avec une bonne vision produit.',
                    'status' => ApplicationEvaluationStatus::SOUMIS,
                    'submitted_at' => now(),
                ]
            );

            $programCriteria = $application->program->evaluationCriteria;
            foreach ($programCriteria as $criterion) {
                ApplicationEvaluationScore::firstOrCreate(
                    [
                        'evaluation_id' => $eval->id,
                        'criterion_id' => $criterion->id,
                    ],
                    [
                        'score' => rand(7, 9),
                        'comment' => 'Critère validé avec de solides arguments.',
                    ]
                );
            }

            $eval->calculateWeightedScore();
        }

        // 5. Enregistrements d'indicateurs de progrès périodiques (IndicatorEntry)
        $indicator = Indicator::where('sae_id', $sae->id)->first();
        $project = Project::where('sae_id', $sae->id)->first();

        if ($indicator && $project) {
            IndicatorEntry::firstOrCreate(
                [
                    'sae_id' => $sae->id,
                    'indicator_id' => $indicator->id,
                    'project_id' => $project->id,
                    'period_on' => now()->subMonths(2)->startOfMonth(),
                ],
                [
                    'value' => 1500000.00,
                    'comment' => 'Mesure initiale au démarrage de la phase 1.',
                    'reported_by_id' => $juryUser?->id,
                    'validated_by_id' => $juryUser?->id,
                    'validated_at' => now()->subMonths(2),
                ]
            );

            IndicatorEntry::firstOrCreate(
                [
                    'sae_id' => $sae->id,
                    'indicator_id' => $indicator->id,
                    'project_id' => $project->id,
                    'period_on' => now()->subMonth()->startOfMonth(),
                ],
                [
                    'value' => 2800000.00,
                    'comment' => 'Progression significative après le lancement commercial.',
                    'reported_by_id' => $juryUser?->id,
                    'validated_by_id' => $juryUser?->id,
                    'validated_at' => now()->subMonth(),
                ]
            );
        }
    }
}
