<?php

namespace App\AccompanimentTemplates;

use App\Enums\DeliverableResponsibleRole;
use App\Enums\DeliverableTarget;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\EvaluationCriterion;
use App\Models\Indicator;
use App\Models\Program;
use App\Models\ProgramPhase;
use App\Models\Sae;
use Illuminate\Support\Facades\DB;

class StartupLoopAfricinnovTemplate implements AccompanimentTemplateInterface
{
    public function getKey(): string
    {
        return 'startup-loop-africinnov';
    }

    public function getTitle(): string
    {
        return 'Startup Loop (Afric\'innov / SAE)';
    }

    public function getSubtitle(): string
    {
        return 'Méthodologie standard d\'incubation & accélération — Phase Pilote 2026-2027';
    }

    public function getDescription(): string
    {
        return 'Dispositif complet structuré en 6 jalons pédagogiques : émargements formations J1-J3 (6 créneaux), 16 séances de coaching individuel (8 C1 + 8 C2), seuil d\'alerte de décrochage à 75%, 20 livrables startups et 42 livrables de gouvernance SAEI.';
    }

    public function getBadge(): string
    {
        return 'Afric\'innov & GIZ';
    }

    public function getDurationWeeks(): int
    {
        return 26; // 6 mois
    }

    public function getPhases(): array
    {
        return [
            [
                'position' => 1,
                'name' => 'Phase 1 : Préparation & Sélection',
                'description' => 'Diagnostic Canvas 6 dimensions, Fiche de sélection, convention d\'incubation et signature des engagements.',
                'objectives' => 'Sélectionner les startups cibles et réaliser l\'audit de maturité initial.',
                'passing_criteria' => 'Diagnostic 6D complété et convention signée.',
                'duration_weeks' => 4,
            ],
            [
                'position' => 2,
                'name' => 'Phase 2 : Formation 1 (PSF)',
                'description' => 'Bootcamp intensif de 3 jours (6 créneaux émargés). Méthodologie Problem-Solution Fit.',
                'objectives' => 'Valider le problème client, le persona, le BMC initial et le plan de test du MVP.',
                'passing_criteria' => 'Assiduité 100% aux 6 créneaux et plan MVP validé.',
                'duration_weeks' => 2,
            ],
            [
                'position' => 3,
                'name' => 'Phase 3 : Coaching 1 PSF Terrain',
                'description' => '8 séances individuelles de coaching bimensuel. Canevas préparatoire avant séance, rapport sous 48h.',
                'objectives' => 'Tester le MVP auprès d\'au moins 10 utilisateurs cibles et itérer.',
                'passing_criteria' => 'Présence >= 75% aux 8 séances de coaching et synthèse des 10 testeurs validée.',
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
                'objectives' => 'Atteindre les métriques de traction, formaliser le pitch deck investisseur et le bilan PMF.',
                'passing_criteria' => 'Présence aux 8 séances et validation du pitch deck final.',
                'duration_weeks' => 8,
            ],
            [
                'position' => 6,
                'name' => 'Phase 6 : Certification & Clôture',
                'description' => 'Auto-évaluation sur les 5 axes, délibération du jury Afric\'innov et plan post-programme à 6 mois.',
                'objectives' => 'Franchir les seuils de passage (70/84/85%) et obtenir la certification.',
                'passing_criteria' => 'Note globale >= 70% et quitus de la SAEI.',
                'duration_weeks' => 2,
            ],
        ];
    }

    public function getStartupDeliverables(): array
    {
        return [
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
    }

    public function getProgrammeDeliverables(): array
    {
        return [
            ['seq' => 1, 'phase' => 1, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Fiches de sélection et procès-verbal du comité'],
            ['seq' => 2, 'phase' => 1, 'role' => DeliverableResponsibleRole::SAEI, 'name' => 'Conventions d\'incubation signées (Lot complet)'],
            ['seq' => 3, 'phase' => 1, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport consolidé du diagnostic initial des startups'],
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
    }

    public function getEvaluationCriteria(): array
    {
        return [
            [
                'name' => 'Clarté du problème & Proposition de valeur (Problem-Solution Fit)',
                'description' => 'Pertinence du besoin non résolu et différenciation par rapport aux alternatives.',
                'weight' => 3,
                'max_score' => 10,
                'position' => 1,
            ],
            [
                'name' => 'Complémentarité et leadership de l\'équipe fondatrice',
                'description' => 'Compétences techniques, posture entrepreneuriale et disponibilité à plein temps.',
                'weight' => 3,
                'max_score' => 10,
                'position' => 2,
            ],
            [
                'name' => 'Preuve de concept ou retours terrain initiaux',
                'description' => 'Premiers prototypes, entretiens clients réalisés ou signaux de marché.',
                'weight' => 2,
                'max_score' => 10,
                'position' => 3,
            ],
            [
                'name' => 'Potentiel de passage à l\'échelle & impact',
                'description' => 'Taille du marché adressable, viabilité économique et création d\'emplois.',
                'weight' => 2,
                'max_score' => 10,
                'position' => 4,
            ],
        ];
    }

    public function getMneIndicators(): array
    {
        return [
            [
                'name' => 'Taux d\'assiduité aux séances de coaching',
                'definition' => 'Pourcentage des 16 séances de coaching individuel effectivement honorées par startup.',
                'formula' => 'Séances réalisées / 16 séances prévues * 100',
                'category' => 'MISE_EN_OEUVRE',
                'target' => 85,
                'unit' => '%',
            ],
            [
                'name' => 'Taux de conformité des livrables',
                'definition' => 'Pourcentage des livrables méthodologiques validés sans retard critique.',
                'formula' => 'Livrables validés / 20 livrables attendus * 100',
                'category' => 'LIVRABLES',
                'target' => 80,
                'unit' => '%',
            ],
            [
                'name' => 'Volume horaire effectif délivré',
                'definition' => 'Cumul des heures de coaching individuel (16h par startup visées).',
                'formula' => 'Somme des heures de coaching enregistrées',
                'category' => 'QUALITE',
                'target' => 16,
                'unit' => 'heures',
            ],
        ];
    }

    public function getOfficialDriveResources(): array
    {
        return [
            [
                'title' => 'Kit Pédagogique Officiel Afric\'innov & GIZ',
                'description' => 'Dossier Drive contenant l\'ensemble des canevas méthodologiques : Problem-Solution Fit, Product-Market Fit, Empathy Maps et Personas.',
                'url' => 'https://drive.google.com/drive/folders/1AfricinnovStartupLoopKitPedagogiqueExemple',
                'type' => 'FOLDER',
                'is_official' => true,
            ],
            [
                'title' => 'Matrice Excel de Suivi M&E & Feuilles d\'Émargement',
                'description' => 'Classeur de supervision opérationnelle des 16 séances de coaching individuel (8 C1 + 8 C2) et pointage hybride.',
                'url' => 'https://docs.google.com/spreadsheets/d/1AfricinnovSuiviMonitoringEvaluationCoachingExemple',
                'type' => 'SPREADSHEET',
                'is_official' => true,
            ],
            [
                'title' => 'Grille d\'Audit & Diagnostic Initial 6 Dimensions (6D)',
                'description' => 'Grille d\'évaluation de maturité préalable de la startup sur les 6 dimensions clés d\'Afric\'innov.',
                'url' => 'https://docs.google.com/spreadsheets/d/1AfricinnovGrilleAudit6DimensionsExemple',
                'type' => 'SPREADSHEET',
                'is_official' => true,
            ],
        ];
    }

    public function getDetailedGuide(): array
    {
        return [
            'overview' => [
                'origin' => 'Programme Afric\'innov soutenu par l\'Agence Française de Développement (AFD) et la GIZ.',
                'philosophy' => 'Approche itérative centrée sur le Problem-Solution Fit (PSF) puis le Product-Market Fit (PMF), avec obligation de confrontation terrain.',
                'target_audience' => 'Startups innovantes ou projets en amorçage à fort potentiel technologique ou d\'usage en Afrique subsaharienne.',
                'rhythm' => 'Cycle semestriel intensif (26 semaines) combinant 2 bootcamps de formation (F1 & F2) et 16 séances de coaching individuel (C1 & C2).',
            ],
            'rules' => [
                'Seuil de présence minimale' => 'Tout bénéficiaire ayant un taux d\'assiduité inférieur à 75% aux séances de coaching individuel déclenche une alerte de décrochage.',
                'Validation des livrables' => 'Chaque livrable startup doit atteindre une note minimale de 70/100 pour être validé par le coach référent.',
                'Emargements obligatoires' => 'Les 6 créneaux de formation collective (J1-J3 matin/après-midi) font l\'objet d\'une feuille d\'émargement signée et archivée en GED.',
                'Comité de clôture' => 'La certification finale est décernée après franchissement des seuils d\'auto-évaluation sur les 5 axes d\'impact.',
            ],
            'pedagogical_phases' => [
                [
                    'phase' => 'Phase 1 : Préparation & Sélection',
                    'detail' => 'Audit 360° via le Diagnostic Canvas 6 dimensions. Établissement de la fiche d\'engagement et signature de la convention d\'incubation tripartite.',
                    'coach_tips' => 'Vérifier la disponibilité à plein temps des fondateurs et leur clarté sur le problème initial.',
                ],
                [
                    'phase' => 'Phase 2 : Formation 1 (Problem-Solution Fit)',
                    'detail' => 'Bootcamp intensif de 3 jours (6 créneaux émargés). Formalisation du Problem Statement, des personas, de l\'Empathy Map et du plan de test du MVP.',
                    'coach_tips' => 'Insister sur l\'observation terrain des utilisateurs plutôt que sur les suppositions théoriques.',
                ],
                [
                    'phase' => 'Phase 3 : Coaching 1 PSF Terrain',
                    'detail' => '8 séances individuelles de coaching bimensuel. Obligation de déposer un canevas préparatoire 24h avant et un compte-rendu dans les 48h. Validation des retours de 10 testeurs.',
                    'coach_tips' => 'Aider le fondateur à analyser les données qualitatives et décider d\'un pivot si nécessaire.',
                ],
                [
                    'phase' => 'Phase 4 : Formation 2 (Product-Market Fit)',
                    'detail' => 'Bootcamp de 3 jours axé sur les métriques de traction (CAC, LTV, churn), la stratégie d\'acquisition des 100 premiers clients et la modélisation financière.',
                    'coach_tips' => 'Focaliser les exercices sur les canaux d\'acquisition scalables et peu coûteux.',
                ],
                [
                    'phase' => 'Phase 5 : Coaching 2 PMF Terrain',
                    'detail' => '8 séances individuelles orientées exécution commerciale, suivi hebdomadaire de traction et structuration du pitch deck d\'investment readiness.',
                    'coach_tips' => 'Suivre le pipeline commercial et préparer la soutenance devant le jury de certification.',
                ],
                [
                    'phase' => 'Phase 6 : Certification & Clôture',
                    'detail' => 'Auto-évaluation sur les 5 axes d\'impact, délibération du jury Afric\'innov et plan d\'action post-accompagnement à 6 mois.',
                    'coach_tips' => 'Consigner le bilan dans le rapport M&E et formaliser les recommandations pour les bailleurs.',
                ],
            ],
        ];
    }

    public function apply(Sae $sae, Program $program, ?Cohort $cohort = null): array
    {
        return DB::transaction(function () use ($sae, $program, $cohort) {
            $createdCounts = [
                'phases' => 0,
                'startup_deliverables' => 0,
                'programme_deliverables' => 0,
                'criteria' => 0,
                'indicators' => 0,
            ];

            // 1. Phases du programme
            $phaseMap = [];
            foreach ($this->getPhases() as $phaseData) {
                $phase = ProgramPhase::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'position' => $phaseData['position'],
                    ],
                    $phaseData
                );
                $phaseMap[$phaseData['position']] = $phase;
                $createdCounts['phases']++;
            }

            // 2. Synchronisation de la cohorte si fournie
            if ($cohort) {
                $cohort->syncPhasesFromProgram();
            }

            // 3. Livrables Startups
            foreach ($this->getStartupDeliverables() as $item) {
                DeliverableDefinition::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'name' => $item['name'],
                        'target_type' => DeliverableTarget::STARTUP->value,
                    ],
                    [
                        'program_id' => $program->id,
                        'program_phase_id' => $phaseMap[$item['phase']]->id ?? null,
                        'responsible_role' => DeliverableResponsibleRole::FONDATEUR->value,
                        'sequence_order' => $item['seq'],
                        'description' => $item['desc'],
                        'is_required' => true,
                        'max_score' => 100,
                        'passing_score' => 70,
                    ]
                );
                $createdCounts['startup_deliverables']++;
            }

            // 4. Livrables SAEI / Programme
            foreach ($this->getProgrammeDeliverables() as $item) {
                DeliverableDefinition::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'name' => $item['name'],
                        'target_type' => DeliverableTarget::PROGRAMME->value,
                    ],
                    [
                        'program_id' => $program->id,
                        'program_phase_id' => $phaseMap[$item['phase']]->id ?? null,
                        'responsible_role' => $item['role']->value,
                        'sequence_order' => $item['seq'],
                        'is_required' => true,
                        'max_score' => 100,
                        'passing_score' => 70,
                    ]
                );
                $createdCounts['programme_deliverables']++;
            }

            // 5. Critères d'évaluation jury
            foreach ($this->getEvaluationCriteria() as $critData) {
                EvaluationCriterion::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'program_id' => $program->id,
                        'name' => $critData['name'],
                    ],
                    $critData
                );
                $createdCounts['criteria']++;
            }

            // 6. Indicateurs M&E
            foreach ($this->getMneIndicators() as $indData) {
                Indicator::updateOrCreate(
                    [
                        'sae_id' => $sae->id,
                        'name' => $indData['name'],
                    ],
                    $indData
                );
                $createdCounts['indicators']++;
            }

            return $createdCounts;
        });
    }
}
