<?php

namespace Database\Seeders;

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\CoachingSession;
use App\Models\CoachInvitation;
use App\Models\Cohort;
use App\Models\DeliverableDefinition;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Evaluation;
use App\Models\Form;
use App\Models\FormUnlock;
use App\Models\FundingRecord;
use App\Models\Indicator;
use App\Models\Level;
use App\Models\Program;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use App\Models\Resource;
use App\Models\Role;
use App\Models\Sae;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Rôles globaux (sae_id = 0 pour les rôles système)
        setPermissionsTeamId(0);
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $responsableRole = Role::firstOrCreate(['name' => 'responsable', 'guard_name' => 'web']);
        $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
        $entrepreneurRole = Role::firstOrCreate(['name' => 'entrepreneur', 'guard_name' => 'web']);

        // 2. Administrateur Plateforme Hub Africa
        $admin = User::firstOrCreate(
            ['email' => 'admin@hubafrica.org'],
            [
                'name' => 'Directeur Hub Africa',
                'password' => Hash::make('password'),
                'phone' => '+221 77 000 00 01',
                'status' => 'ACTIVE',
            ]
        );
        $admin->assignRole($superAdminRole);

        // 3. Responsable SAE 1 : Fatou Diop (Teranga Africa Hub)
        $fatou = User::firstOrCreate(
            ['email' => 'fatou.diop@teranga.sn'],
            [
                'name' => 'Fatou Diop',
                'password' => Hash::make('password'),
                'phone' => '+221 77 123 45 67',
                'status' => 'ACTIVE',
            ]
        );

        // Création SAE 1
        $teranga = Sae::firstOrCreate(
            ['slug' => 'teranga-africa'],
            [
                'owner_id' => $fatou->id,
                'name' => 'Teranga Africa Hub',
                'description' => 'Incubateur panafricain d\'innovation durable et de technologies vertes.',
                'email' => 'contact@teranga.sn',
                'phone' => '+221 33 800 00 00',
                'address' => 'Point E, Boulevard de l\'Est, Dakar, Sénégal',
                'status' => 'ACTIVE',
            ]
        );
        $fatou->saes()->syncWithoutDetaching([$teranga->id => ['role' => 'RESPONSABLE', 'status' => 'ACTIVE']]);

        setPermissionsTeamId($teranga->id);
        $fatou->assignRole($responsableRole);

        // 4. Responsable SAE 2 : Amadou Koné (Jokkolabs Abidjan)
        $amadou = User::firstOrCreate(
            ['email' => 'amadou.kone@jokkolabs.ci'],
            [
                'name' => 'Amadou Koné',
                'password' => Hash::make('password'),
                'phone' => '+225 07 00 11 22',
                'status' => 'ACTIVE',
            ]
        );

        $jokkolabs = Sae::firstOrCreate(
            ['slug' => 'jokkolabs-abidjan'],
            [
                'owner_id' => $amadou->id,
                'name' => 'Jokkolabs Abidjan',
                'description' => 'Pôle d\'incubation et d\'accélération d\'entreprises tech & agro-business.',
                'email' => 'abidjan@jokkolabs.ci',
                'phone' => '+225 27 20 00 00',
                'address' => 'Cocody Deux-Plateaux, Abidjan, Côte d\'Ivoire',
                'status' => 'ACTIVE',
            ]
        );
        $amadou->saes()->syncWithoutDetaching([$jokkolabs->id => ['role' => 'RESPONSABLE', 'status' => 'ACTIVE']]);

        setPermissionsTeamId($jokkolabs->id);
        $amadou->assignRole($responsableRole);

        // Contexte Teranga Africa
        setPermissionsTeamId($teranga->id);

        // 5. Niveaux de maturité (Levels) pour Teranga Africa
        $lvlIdeation = Level::create([
            'sae_id' => $teranga->id,
            'name' => '1. Idéation',
            'order' => 1,
            'color' => '#f59e0b',
            'description' => 'Validation de l\'idée et proposition de valeur initiale',
            'min_average' => 50.00,
        ]);
        $lvlIncubation = Level::create([
            'sae_id' => $teranga->id,
            'name' => '2. Incubation',
            'order' => 2,
            'color' => '#0d9488',
            'description' => 'Développement du prototype (MVP) et validation marché',
            'min_average' => 65.00,
        ]);
        $lvlAcceleration = Level::create([
            'sae_id' => $teranga->id,
            'name' => '3. Accélération',
            'order' => 3,
            'color' => '#6366f1',
            'description' => 'Croissance commerciale et levée de fonds d\'amorçage',
            'min_average' => 75.00,
        ]);

        // 6. Programmes & Cohortes
        $programGreen = Program::create([
            'sae_id' => $teranga->id,
            'name' => 'GreenTech Sénégal 2026',
            'description' => 'Programme d\'incubation dédié aux startups de la transition écologique et de l\'économie circulaire.',
            'objectives' => 'Accompagner 15 startups vers leur premier tour de table et leur conformité RSE.',
            'target' => 'Startups vertes sénégalaises',
            'capacity' => 15,
            'start_date' => now()->startOfYear(),
            'end_date' => now()->addMonths(6),
            'registration_start' => now()->subMonths(3),
            'registration_end' => now()->subMonth(),
            'level_id' => $lvlIncubation->id,
            'status' => 'LANCE',
        ]);

        $cohortGreen1 = Cohort::create([
            'sae_id' => $teranga->id,
            'program_id' => $programGreen->id,
            'name' => 'Cohorte Dakar Verte 2026-1',
            'number' => 1,
            'capacity' => 12,
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'objectives' => 'Finaliser les MVP et tester 3 canaux de distribution',
            'status' => 'EN_COURS',
        ]);

        // 7. Coachs
        // Coach 1 : Marie Sarr
        $userCoach1 = User::create([
            'name' => 'Marie Sarr',
            'email' => 'marie.sarr@coach.sn',
            'password' => Hash::make('password'),
            'phone' => '+221 77 444 55 66',
            'status' => 'ACTIVE',
        ]);
        $userCoach1->assignRole($coachRole);
        $userCoach1->saes()->syncWithoutDetaching([$teranga->id => ['role' => 'COACH', 'status' => 'ACTIVE']]);

        $coach1 = Coach::create([
            'sae_id' => $teranga->id,
            'user_id' => $userCoach1->id,
            'name' => 'Marie Sarr',
            'email' => 'marie.sarr@coach.sn',
            'phone' => '+221 77 444 55 66',
            'specialties' => ['Finance', 'Business Model', 'Gouvernance'],
            'availability' => 'Temps partiel (15h/semaine)',
            'max_capacity' => 8,
            'status' => 'ACTIVE',
        ]);

        // Coach 2 : Ibrahima Diallo
        $userCoach2 = User::create([
            'name' => 'Ibrahima Diallo',
            'email' => 'ibrahima.diallo@coach.sn',
            'password' => Hash::make('password'),
            'phone' => '+221 77 777 88 99',
            'status' => 'ACTIVE',
        ]);
        $userCoach2->assignRole($coachRole);
        $userCoach2->saes()->syncWithoutDetaching([$teranga->id => ['role' => 'COACH', 'status' => 'ACTIVE']]);

        $coach2 = Coach::create([
            'sae_id' => $teranga->id,
            'user_id' => $userCoach2->id,
            'name' => 'Ibrahima Diallo',
            'email' => 'ibrahima.diallo@coach.sn',
            'phone' => '+221 77 777 88 99',
            'specialties' => ['Marketing Digital', 'Pitch', 'Agro-industrie'],
            'availability' => 'Plein temps',
            'max_capacity' => 10,
            'status' => 'ACTIVE',
        ]);

        // 8. Invitation d'un Coach en attente (Test du flux lien d'invitation)
        CoachInvitation::create([
            'sae_id' => $teranga->id,
            'email' => 'dr.koffi@expert.ci',
            'name' => 'Dr. Kouamé Koffi',
            'token' => 'demo-coach-invite-token-2026',
            'specialties' => ['Stratégie Panafricaine', 'Exportation ZLECAF'],
            'max_capacity' => 6,
            'is_external' => true,
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
            'created_by' => $fatou->id,
        ]);

        // 9. Entrepreneurs & Projets
        // Entrepreneur 1 : Ousmane Fall (EcoBat)
        $userEnt1 = User::create([
            'name' => 'Ousmane Fall',
            'email' => 'ousmane.fall@ecobat.sn',
            'password' => Hash::make('password'),
            'phone' => '+221 77 888 11 22',
            'status' => 'ACTIVE',
        ]);
        $userEnt1->assignRole($entrepreneurRole);
        $userEnt1->saes()->syncWithoutDetaching([$teranga->id => ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']]);

        $ent1 = Entrepreneur::create([
            'sae_id' => $teranga->id,
            'user_id' => $userEnt1->id,
            'first_name' => 'Ousmane',
            'last_name' => 'Fall',
            'email' => 'ousmane.fall@ecobat.sn',
            'phone' => '+221 77 888 11 22',
            'gender' => 'M',
            'birth_date' => '1992-04-12',
            'country' => 'Sénégal',
            'territory' => 'Région de Dakar',
            'education_level' => 'Master Génie Civil',
            'profession' => 'Ingénieur Matériaux',
            'status' => 'ACTIVE',
        ]);

        $project1 = Project::create([
            'sae_id' => $teranga->id,
            'entrepreneur_id' => $ent1->id,
            'name' => 'EcoBat Sénégal',
            'description' => 'Fabrication de briques compressées écologiques à base de terre cuite et de résidus agricoles.',
            'problem' => 'Coût prohibitif du ciment importé et empreinte carbone désastreuse des constructions urbaines.',
            'solution' => 'Briques écologiques isolantes, produites localement et 40% moins chères que le parpaing traditionnel.',
            'product_service' => 'Briques de terre compressée (BTC) et pavés recyclés.',
            'value_proposition' => 'Construction durable, thermique naturelle, réduction de 60% des émissions de CO2.',
            'target' => 'Promoteurs immobiliers et auto-constructeurs éco-responsables.',
            'sector' => 'Construction durable & BTP',
            'territory' => 'Dakar & Thiès',
            'sdgs' => [11, 12, 13],
            'team_size' => 4,
            'status' => 'ACTIF',
            'current_level_id' => $lvlIncubation->id,
            'current_level_entered_at' => now()->subMonth(),
        ]);

        // Historique de niveau
        ProjectLevelHistory::create([
            'sae_id' => $teranga->id,
            'project_id' => $project1->id,
            'from_level_id' => $lvlIdeation->id,
            'to_level_id' => $lvlIncubation->id,
            'reason' => 'Validation du prototype de brique en laboratoire et 2 premières commandes pilotes.',
            'started_at' => now()->subMonth(),
            'validated_by' => $fatou->id,
        ]);

        // Inscription en cohorte
        Enrollment::create([
            'sae_id' => $teranga->id,
            'cohort_id' => $cohortGreen1->id,
            'project_id' => $project1->id,
            'entrepreneur_id' => $ent1->id,
            'status' => 'ACTIF',
        ]);

        // Affectation du coach Marie Sarr à EcoBat
        CoachAssignment::create([
            'sae_id' => $teranga->id,
            'coach_id' => $coach1->id,
            'project_id' => $project1->id,
            'program_id' => $programGreen->id,
            'cohort_id' => $cohortGreen1->id,
            'assigned_at' => now()->subMonth(),
            'status' => 'ACTIF',
        ]);

        // Entrepreneur 2 : Awa Ndiaye (SunuAgri Tech)
        $userEnt2 = User::create([
            'name' => 'Awa Ndiaye',
            'email' => 'awa.ndiaye@agritech.sn',
            'password' => Hash::make('password'),
            'phone' => '+221 76 555 44 33',
            'status' => 'ACTIVE',
        ]);
        $userEnt2->assignRole($entrepreneurRole);
        $userEnt2->saes()->syncWithoutDetaching([$teranga->id => ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']]);

        $ent2 = Entrepreneur::create([
            'sae_id' => $teranga->id,
            'user_id' => $userEnt2->id,
            'first_name' => 'Awa',
            'last_name' => 'Ndiaye',
            'email' => 'awa.ndiaye@agritech.sn',
            'phone' => '+221 76 555 44 33',
            'gender' => 'F',
            'birth_date' => '1995-08-23',
            'country' => 'Sénégal',
            'territory' => 'Vallée du Fleuve Sénégal',
            'education_level' => 'Ingénieur Agronome',
            'profession' => 'Agri-entrepreneure',
            'status' => 'ACTIVE',
        ]);

        $project2 = Project::create([
            'sae_id' => $teranga->id,
            'entrepreneur_id' => $ent2->id,
            'name' => 'SunuAgri Tech',
            'description' => 'Plateforme mobile et capteurs connectés pour l\'irrigation de précision et la lutte contre le gaspillage de l\'eau agricole.',
            'problem' => 'Sécheresses récurrentes et pertes de 45% des récoltes dues au mauvais dosage d\'eau.',
            'solution' => 'Sondes d\'humidité basse consommation connectées par SMS/USSD aux smartphones des producteurs.',
            'product_service' => 'Kit de sondes solaires + application mobile prédictive.',
            'value_proposition' => 'Économie de 35% d\'eau et hausse de 20% des rendements maraîchers.',
            'target' => 'Coopératives maraîchères et petits exploitants.',
            'sector' => 'AgriTech & Eau',
            'territory' => 'Saint-Louis & Matam',
            'sdgs' => [2, 6, 9],
            'team_size' => 3,
            'status' => 'ACTIF',
            'current_level_id' => $lvlIdeation->id,
            'current_level_entered_at' => now()->subMonths(2),
        ]);

        CoachAssignment::create([
            'sae_id' => $teranga->id,
            'coach_id' => $coach2->id,
            'project_id' => $project2->id,
            'program_id' => $programGreen->id,
            'assigned_at' => now()->subMonth(),
            'status' => 'ACTIF',
        ]);

        // 10. Livrables et Évaluations
        $defBmc = DeliverableDefinition::create([
            'sae_id' => $teranga->id,
            'program_id' => $programGreen->id,
            'level_id' => $lvlIncubation->id,
            'name' => 'Business Model Canvas (BMC) Validé',
            'description' => 'Document de synthèse présentant les 9 blocs du modèle économique avec validation client.',
            'instructions' => 'Remplir le canvas et joindre le compte-rendu de 10 entretiens prospects.',
            'is_required' => true,
            'max_score' => 100,
            'passing_score' => 70,
            'max_attempts' => 3,
            'deadline' => now()->addDays(20),
        ]);

        // Soumission EcoBat
        $sub1 = DeliverableSubmission::create([
            'sae_id' => $teranga->id,
            'definition_id' => $defBmc->id,
            'project_id' => $project1->id,
            'entrepreneur_id' => $ent1->id,
            'submitted_by_id' => $userEnt1->id,
            'version' => 1,
            'file_name' => 'BMC_EcoBat_v1.pdf',
            'comment' => 'Voici notre BMC révisé suite aux échanges avec les promoteurs.',
            'status' => 'VALIDE',
            'submitted_at' => now()->subDays(5),
        ]);

        Evaluation::create([
            'sae_id' => $teranga->id,
            'submission_id' => $sub1->id,
            'evaluator_id' => $userCoach1->id,
            'score' => 84.50,
            'feedback' => 'Excellent travail sur les canaux de distribution. La structure de coûts est claire.',
            'status' => 'VALIDE',
            'evaluated_at' => now()->subDays(2),
        ]);

        // 11. Séances de Coaching
        CoachingSession::create([
            'sae_id' => $teranga->id,
            'coach_id' => $coach1->id,
            'project_id' => $project1->id,
            'entrepreneur_id' => $ent1->id,
            'date' => now()->subDays(3),
            'duration' => 60,
            'type' => 'INDIVIDUEL',
            'theme' => 'Revue du modèle financier et politique tarifaire',
            'objectives' => 'Définir la marge brute par palette de briques et valider le seuil de rentabilité.',
            'report' => 'Marge unitaire calculée à 28%. Discussion approfondie sur le fonds de roulement initial nécessaire.',
            'next_actions' => 'Finaliser la grille tarifaire grossistes avant le 15 du mois.',
            'status' => 'REALISEE',
        ]);

        CoachingSession::create([
            'sae_id' => $teranga->id,
            'coach_id' => $coach2->id,
            'project_id' => $project2->id,
            'entrepreneur_id' => $ent2->id,
            'date' => now()->addDays(4),
            'duration' => 90,
            'type' => 'ATELIER',
            'theme' => 'Stratégie de déploiement terrain des capteurs',
            'objectives' => 'Planifier les 5 tests pilotes dans les exploitations de Richard-Toll.',
            'status' => 'PLANIFIEE',
        ]);

        // 12. Financements
        FundingRecord::create([
            'sae_id' => $teranga->id,
            'project_id' => $project1->id,
            'amount_sought' => 25000000.00,
            'amount_obtained' => 15000000.00,
            'amount_invested' => 10000000.00,
            'source' => 'Fonds Vert Climat Sénégal (Subvention)',
            'type' => 'GRANT',
            'status' => 'DECAISSE',
            'date' => now()->subMonths(2),
            'justification' => 'Financement d\'acquisition de la presse hydraulique mobile.',
            'verified' => true,
        ]);

        // 13. Indicateurs de Suivi-Évaluation (S&E)
        Indicator::create([
            'sae_id' => $teranga->id,
            'name' => 'Taux d\'admission en cohorte',
            'definition' => 'Part des candidatures acceptées sur l\'ensemble des candidatures examinées.',
            'formula' => '(Candidatures acceptées / Candidatures examinées) * 100',
            'category' => 'PORTEFEUILLE',
            'unit' => '%',
            'target' => 25.00,
            'current' => 28.00,
            'previous' => 22.00,
            'status' => 'ATTEINT',
        ]);

        Indicator::create([
            'sae_id' => $teranga->id,
            'name' => 'Part des femmes entrepreneures',
            'definition' => 'Proportion d\'entreprises dirigées par des femmes dans le portefeuille actif.',
            'formula' => '(Entrepreneures femmes / Total entrepreneurs) * 100',
            'category' => 'INCLUSION',
            'unit' => '%',
            'target' => 50.00,
            'current' => 50.00,
            'previous' => 45.00,
            'status' => 'ATTEINT',
        ]);

        Indicator::create([
            'sae_id' => $teranga->id,
            'name' => 'Taux de validation des livrables',
            'definition' => 'Pourcentage des soumissions de livrables ayant reçu une évaluation favorable.',
            'formula' => '(Livrables validés / Livrables déposés) * 100',
            'category' => 'LIVRABLES',
            'unit' => '%',
            'target' => 80.00,
            'current' => 85.00,
            'previous' => 70.00,
            'status' => 'ATTEINT',
        ]);

        // 14. Ressources partagées
        Resource::create([
            'sae_id' => $teranga->id,
            'title' => 'Guide méthodologique du Business Model Canvas Africain',
            'description' => 'Manuel pratique pour adapter son BMC aux réalités des marchés informels et des flux de paiement mobile.',
            'type' => 'DOCUMENT',
            'category' => 'Entrepreneuriat',
            'author' => 'Hub Africa Knowledge Hub',
            'visibility' => 'TOUS',
        ]);

        // 15. Événements Timeline
        TimelineEvent::create([
            'sae_id' => $teranga->id,
            'project_id' => $project1->id,
            'entrepreneur_id' => $ent1->id,
            'type' => 'NIVEAU',
            'title' => 'Passage au niveau Incubation',
            'description' => 'Le projet EcoBat a franchi les jalons de validation du niveau Idéation.',
            'icon' => 'trending-up',
        ]);

        // 16. Formulaires d'évaluation & Enquêtes CUBE S&E
        $baselineForm = Form::firstOrCreate(
            ['slug' => 'diagnostic-initial-baseline'],
            [
                'sae_id' => $teranga->id,
                'program_id' => $programGreen->id,
                'name' => 'Diagnostic Initial & Cadrage (Baseline)',
                'description' => 'Évaluation approfondie de la maturité commerciale, technique et financière du projet à l\'entrée dans le programme.',
                'type' => FormType::BASELINE,
                'is_mandatory' => true,
                'is_active' => true,
                'fields' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'key' => 'chiffre_affaires_actuel',
                            'label' => 'Chiffre d\'affaires mensuel moyen actuel (FCFA)',
                            'required' => true,
                        ],
                    ],
                    [
                        'type' => 'number',
                        'data' => [
                            'key' => 'nombre_salaries',
                            'label' => 'Nombre d\'emplois permanents à ce jour',
                            'required' => true,
                        ],
                    ],
                    [
                        'type' => 'select',
                        'data' => [
                            'key' => 'stade_produit',
                            'label' => 'Stade de développement de l\'offre',
                            'options' => [
                                ['value' => 'ideation', 'label' => 'Idéation / Concept'],
                                ['value' => 'prototype', 'label' => 'Prototype / MVP testé'],
                                ['value' => 'commercialise', 'label' => 'Produit commercialisé'],
                                ['value' => 'croissance', 'label' => 'En phase de croissance'],
                            ],
                            'required' => true,
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'data' => [
                            'key' => 'principaux_defis',
                            'label' => 'Quels sont les 3 freins majeurs que vous souhaitez lever avec la SAE ?',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );

        $satisfactionForm = Form::firstOrCreate(
            ['slug' => 'enquete-satisfaction-mi-parcours'],
            [
                'sae_id' => $teranga->id,
                'program_id' => $programGreen->id,
                'name' => 'Enquête de satisfaction mi-parcours',
                'description' => 'Recueil des retours d\'expérience des entrepreneurs sur les coachings et ateliers reçus.',
                'type' => FormType::SATISFACTION,
                'is_mandatory' => false,
                'is_active' => true,
                'fields' => [
                    [
                        'type' => 'rating',
                        'data' => [
                            'key' => 'satisfaction_globale',
                            'label' => 'Satisfaction globale vis-à-vis de l\'accompagnement (sur 5)',
                            'required' => true,
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'data' => [
                            'key' => 'points_forts',
                            'label' => 'Quels sont les aspects les plus utiles de votre accompagnement ?',
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'data' => [
                            'key' => 'suggestions',
                            'label' => 'Avez-vous des suggestions d\'amélioration ?',
                        ],
                    ],
                ],
            ]
        );

        $endlineForm = Form::firstOrCreate(
            ['slug' => 'bilan-final-sortie-endline'],
            [
                'sae_id' => $teranga->id,
                'program_id' => $programGreen->id,
                'name' => 'Bilan final & Sortie de programme (Endline)',
                'description' => 'Mesure des résultats finaux, des financements mobilisés et de l\'autonomie acquise par l\'entreprise.',
                'type' => FormType::ENDLINE,
                'is_mandatory' => true,
                'is_active' => true,
                'fields' => [
                    [
                        'type' => 'text',
                        'data' => [
                            'key' => 'ca_sortie',
                            'label' => 'Chiffre d\'affaires annuel réalisé à la sortie (FCFA)',
                            'required' => true,
                        ],
                    ],
                    [
                        'type' => 'number',
                        'data' => [
                            'key' => 'emplois_crees_nets',
                            'label' => 'Nouveaux emplois créés durant l\'accompagnement',
                            'required' => true,
                        ],
                    ],
                ],
            ]
        );

        // Assignation / Déblocage pour Ousmane Fall (ent1)
        FormUnlock::firstOrCreate(
            [
                'form_id' => $baselineForm->id,
                'entrepreneur_id' => $ent1->id,
            ],
            [
                'sae_id' => $teranga->id,
                'unlocked_by_id' => $fatou->id,
                'status' => FormUnlockStatus::EN_ATTENTE,
                'due_date' => now()->addDays(5),
                'note' => 'Merci de compléter ce diagnostic initial avant la prochaine séance de coaching individuel avec votre mentor.',
            ]
        );

        FormUnlock::firstOrCreate(
            [
                'form_id' => $satisfactionForm->id,
                'entrepreneur_id' => $ent1->id,
            ],
            [
                'sae_id' => $teranga->id,
                'unlocked_by_id' => $fatou->id,
                'status' => FormUnlockStatus::EN_ATTENTE,
                'due_date' => now()->addDays(12),
                'note' => 'Votre avis compte pour adapter les prochains ateliers.',
            ]
        );
    }
}
