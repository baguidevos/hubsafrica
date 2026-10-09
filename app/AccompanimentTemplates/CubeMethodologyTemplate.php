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

class CubeMethodologyTemplate implements AccompanimentTemplateInterface
{
    public function getKey(): string
    {
        return 'cube-saei-kpital';
    }

    public function getTitle(): string
    {
        return 'Méthodologie CUBE (SAEI CUBE & KPITAL INVEST)';
    }

    public function getSubtitle(): string
    {
        return 'Diagnostic PAS, Modèle Économique 17 étapes, OHADA & Levée de fonds';
    }

    public function getDescription(): string
    {
        return 'Dispositif complet de la SAEI CUBE structuré en 6 jalons : Diagnostic d\'entrée (PAS), validation du Modèle Économique en 17 étapes, Organisation & RH, Gestion Financière & Droit OHADA, Préparation intensive à l\'investissement (#KPITAL_INVEST) avec Virtual Data Room (VDR) et Connexion Business Angels.';
    }

    public function getBadge(): string
    {
        return 'CUBE & KPITAL INVEST';
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
                'name' => 'Phase 1 : Diagnostic Initial, Sélection & Cadrage (PAS)',
                'description' => 'Diagnostic 360° en ligne, formulation du Plan de Travail Individualisé et du Plan d\'Accompagnement Personnalisé (PAS), convention d\'accompagnement CUBE.',
                'objectives' => 'Évaluer la maturité globale du projet, cerner les besoins spécifiques et formaliser le contrat d\'engagement.',
                'passing_criteria' => 'Diagnostic complété, PAS validé et convention d\'incubation contresignée.',
                'duration_weeks' => 4,
            ],
            [
                'position' => 2,
                'name' => 'Phase 2 : Préincubation — Validation du Modèle Économique Complet',
                'description' => 'Approche Problème & Design Thinking, segmentation client, analyse concurrentielle, proposition de valeur, canaux, CRM, modèle de revenus et avantages compétitifs.',
                'objectives' => 'Valider l\'adéquation Problème-Solution et formaliser les 17 composantes fondamentales du Business Model.',
                'passing_criteria' => 'Business Model Canvas complet validé et entretiens de validation marché réalisés.',
                'duration_weeks' => 6,
            ],
            [
                'position' => 3,
                'name' => 'Phase 3 : Incubation — Organisation, Gouvernance & Équipe',
                'description' => 'Compétences de l\'entrepreneur (discipline), organigramme d\'entreprise, gestion des RH (dirigeants, employés, board), stratégie RSE, propriété intellectuelle et planification des activités clés.',
                'objectives' => 'Structurer l\'organisation interne, clarifier les rôles, sécuriser les actifs immatériels et ancrer la responsabilité sociétale.',
                'passing_criteria' => 'Organigramme validé, fiches de poste formalisées et charte RSE adoptée.',
                'duration_weeks' => 4,
            ],
            [
                'position' => 4,
                'name' => 'Phase 4 : Incubation — Gestion Financière, Comptabilité & Juridique OHADA',
                'description' => 'Gestion de trésorerie, élaboration des prévisions financières à 3 ans, tenue d\'une comptabilité probante, choix de la forme juridique, droit des affaires OHADA et contrats d\'affaires.',
                'objectives' => 'Fiabiliser la gestion financière et comptable pour garantir la traçabilité des opérations et sécuriser le cadre juridique OHADA.',
                'passing_criteria' => 'Modèle financier prévisionnel validé et dossier juridique/comptable conforme aux normes OHADA.',
                'duration_weeks' => 5,
            ],
            [
                'position' => 5,
                'name' => 'Phase 5 : Accélération & #KPITAL_INVEST — Préparation à la Levée de Fonds',
                'description' => 'Auto-évaluation & scoring d\'investissabilité par le porteur, constitution et vérification de la Virtual Data Room (VDR), valorisation d\'entreprise (valeur vs prix) et Pitch Deck (10-15 slides).',
                'objectives' => 'Structurer une stratégie de mobilisation de capitaux, constituer une Data Room irréprochable et préparer l\'entrepreneur aux Due Diligences.',
                'passing_criteria' => 'Checklist Virtual Data Room 100% validée, valorisation argumentée et Pitch Deck investisseur finalisé.',
                'duration_weeks' => 5,
            ],
            [
                'position' => 6,
                'name' => 'Phase 6 : Connexion Investisseurs, Demo Day & Clôture',
                'description' => 'Présentations continues devant le réseau de Business Angels (+50 membres), négociations de partenariats financiers, grand événement annuel de clôture et plan post-programme.',
                'objectives' => 'Connecter les startups aux investisseurs structurants, délivrer les certifications CUBE ACADEMY et planifier la trajectoire de passage à l\'échelle.',
                'passing_criteria' => 'Pitch délivré devant le comité investisseurs et bilan de sortie validé.',
                'duration_weeks' => 2,
            ],
        ];
    }

    public function getStartupDeliverables(): array
    {
        return [
            // Phase 1 : Diagnostic & Cadrage
            ['seq' => 1, 'phase' => 1, 'name' => 'Formulaire de Diagnostic Initial d\'Entrée (Diagnostic 360°)', 'desc' => 'Auto-évaluation initiale du projet et analyse des besoins d\'accompagnement'],
            ['seq' => 2, 'phase' => 1, 'name' => 'Plan d\'Accompagnement Personnalisé (PAS) & Plan de Travail', 'desc' => 'Feuille de route individualisée et objectifs opérationnels'],
            ['seq' => 3, 'phase' => 1, 'name' => 'Convention d\'accompagnement CUBE & Charte d\'engagement', 'desc' => 'Contrat d\'incubation signé et engagement sur les valeurs CUBE'],

            // Phase 2 : Modèle Économique Complet
            ['seq' => 4, 'phase' => 2, 'name' => 'Définition de la Problématique & Fiche Approche Design Thinking', 'desc' => 'Validation du problème client profond et démarche centrée utilisateur'],
            ['seq' => 5, 'phase' => 2, 'name' => 'Étude de Marché & Segmentation de la Clientèle Cible', 'desc' => 'Découpage du marché en segments homogènes et analyse du persona'],
            ['seq' => 6, 'phase' => 2, 'name' => 'Matrice d\'Analyse Concurrentielle Directe & Indirecte', 'desc' => 'Positionnement stratégique et cartographie des offres alternatives'],
            ['seq' => 7, 'phase' => 2, 'name' => 'Proposition de Valeur Formalisée & Fiche Solution / Produit (MVP)', 'desc' => 'Promesse de valeur délivrée et spécifications du produit ou service'],
            ['seq' => 8, 'phase' => 2, 'name' => 'Plan des Canaux de Distribution & Stratégie CRM/GRC', 'desc' => 'Schéma logistique, canaux de vente et gestion de la relation client'],
            ['seq' => 9, 'phase' => 2, 'name' => 'Modèle Économique Complet en 17 Étapes (Business Model validé)', 'desc' => 'Synthèse des flux de revenus, structure de coûts et avantages compétitifs'],

            // Phase 3 : Organisation, Gouvernance & Équipe
            ['seq' => 10, 'phase' => 3, 'name' => 'Organigramme d\'Entreprise & Cartographie des Compétences RH', 'desc' => 'Structuration de la hiérarchie et organisation des responsabilités'],
            ['seq' => 11, 'phase' => 3, 'name' => 'Dossier RH de l\'Équipe Dirigeante (CVs & Fiches de Postes)', 'desc' => 'Profils des associés, employés clés et gouvernance du board'],
            ['seq' => 12, 'phase' => 3, 'name' => 'Charte de Responsabilité Sociétale des Entreprises (Stratégie RSE)', 'desc' => 'Engagements environnementaux, sociétaux et éthiques de l\'entreprise'],
            ['seq' => 13, 'phase' => 3, 'name' => 'Dossier de Protection de la Propriété Intellectuelle', 'desc' => 'Dépôt de marque, protection des innovations et accords de confidentialité'],
            ['seq' => 14, 'phase' => 3, 'name' => 'Plan Opérationnel des Activités Clés & Gestion des Stocks', 'desc' => 'Planification d\'exécution, calendrier de production et gestion logistique'],

            // Phase 4 : Gestion Financière, Comptabilité & Juridique OHADA
            ['seq' => 15, 'phase' => 4, 'name' => 'Modèle Financier Prévisionnel à 3 Ans & Plan de Trésorerie', 'desc' => 'Budgétisation complète, seuil de rentabilité et projections de trésorerie'],
            ['seq' => 16, 'phase' => 4, 'name' => 'Dossier de Suivi Comptable & Traçabilité des Pièces Justificatives', 'desc' => 'Mise en place des registres d\'achats/ventes et traçabilité des opérations'],
            ['seq' => 17, 'phase' => 4, 'name' => 'Dossier Juridique de Constitution & Conformité Droit des Affaires OHADA', 'desc' => 'Statuts formalisés, immatriculation RCCM/NIF et conformité OHADA'],
            ['seq' => 18, 'phase' => 4, 'name' => 'Recueil des Contrats d\'Affaires Types & Conventions Partenaires', 'desc' => 'Contrats clients, fournisseurs, prestataires et pacte d\'actionnaires'],

            // Phase 5 : KPITAL INVEST & Levée de Fonds
            ['seq' => 19, 'phase' => 5, 'name' => 'Formulaire d\'Auto-Évaluation & Scoring d\'Investissabilité (#KPITAL_INVEST)', 'desc' => 'Évaluation du projet par le porteur avec preuves et retours critiques'],
            ['seq' => 20, 'phase' => 5, 'name' => 'Checklist Complétée de la Virtual Data Room (VDR)', 'desc' => 'Vérification de complétude des documents commerciaux, légaux et financiers'],
            ['seq' => 21, 'phase' => 5, 'name' => 'Note Technique de Valorisation d\'Entreprise (Calculs & Justifications)', 'desc' => 'Méthodes de valorisation retenues pour appuyer la négociation d\'apport'],
            ['seq' => 22, 'phase' => 5, 'name' => 'Pitch Deck Investisseur Professionnel (10 à 15 Slides)', 'desc' => 'Support visuel synthétique adapté aux Business Angels et fonds de capital'],

            // Phase 6 : Clôture & Passage à l'Échelle
            ['seq' => 23, 'phase' => 6, 'name' => 'Résumé Exécutif Investisseurs (One-Pager Synthétique)', 'desc' => 'Fiche synthétique percutante pour diffusion aux partenaires financiers'],
            ['seq' => 24, 'phase' => 6, 'name' => 'Plan d\'Action Post-Incubation & Feuille de Route à 12 Mois', 'desc' => 'Objectifs d\'autonomie financière, expansion marché et recrutement post-CUBE'],
        ];
    }

    public function getProgrammeDeliverables(): array
    {
        return [
            ['seq' => 1, 'phase' => 1, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Rapport consolidé de sélection & Synthèse des diagnostics d\'entrée'],
            ['seq' => 2, 'phase' => 1, 'role' => DeliverableResponsibleRole::SAEI, 'name' => 'Conventions d\'accompagnement CUBE contresignées (Lot promotion)'],
            ['seq' => 3, 'phase' => 1, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Registre des Plans d\'Accompagnement Personnalisés (PAS) validés'],
            ['seq' => 4, 'phase' => 2, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Émargements des ateliers Business Model & Design Thinking (CUBE ACADEMY)'],
            ['seq' => 5, 'phase' => 2, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Synthèse des revues individuelles de modélisation économique (17 étapes)'],
            ['seq' => 6, 'phase' => 2, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport de validation de l\'adéquation Problème-Solution'],
            ['seq' => 7, 'phase' => 3, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Comptes-rendus des séances de coaching Organisation, Gouvernance & RH'],
            ['seq' => 8, 'phase' => 3, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Grille d\'évaluation de la gouvernance et de la conformité RSE'],
            ['seq' => 9, 'phase' => 4, 'role' => DeliverableResponsibleRole::FORMATEUR, 'name' => 'Feuilles d\'émargement des séminaires Gestion Comptable & Droit OHADA'],
            ['seq' => 10, 'phase' => 4, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport d\'audit de conformité juridique et financière des entreprises'],
            ['seq' => 11, 'phase' => 5, 'role' => DeliverableResponsibleRole::COACH, 'name' => 'Fiches de validation de la Virtual Data Room (VDR) & Valorisations'],
            ['seq' => 12, 'phase' => 5, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Pipeline officiel des startups investissables #KPITAL_INVEST'],
            ['seq' => 13, 'phase' => 6, 'role' => DeliverableResponsibleRole::COORDINATEUR, 'name' => 'Procès-verbal du comité d\'investissement & Rencontres Business Angels'],
            ['seq' => 14, 'phase' => 6, 'role' => DeliverableResponsibleRole::SAEI, 'name' => 'Attestations de fin de formation CUBE ACADEMY & Diplômes d\'incubation'],
            ['seq' => 15, 'phase' => 6, 'role' => DeliverableResponsibleRole::CHARGE_ME, 'name' => 'Rapport annuel de performance, Bilan d\'impact & Restitution bailleurs'],
        ];
    }

    public function getEvaluationCriteria(): array
    {
        return [
            [
                'name' => 'Pertinence du problème & Innovation de la proposition de valeur',
                'description' => 'Compréhension profonde d\'un problème réel africain, différenciation concurrentielle et clarté de la solution.',
                'weight' => 3,
                'max_score' => 10,
                'position' => 1,
            ],
            [
                'name' => 'Cohérence & Viabilité du Modèle Économique (17 étapes)',
                'description' => 'Solidité des flux de revenus, structure des coûts maîtrisée, pertinence des canaux de distribution et relation client.',
                'weight' => 3,
                'max_score' => 10,
                'position' => 2,
            ],
            [
                'name' => 'Compétences, Discipline & Leadership de l\'équipe fondatrice',
                'description' => 'Complémentarité des profils, posture entrepreneuriale, assiduité et respect des engagements contractuels.',
                'weight' => 2,
                'max_score' => 10,
                'position' => 3,
            ],
            [
                'name' => 'Conformité légale OHADA, Gestion financière & RSE',
                'description' => 'Régularité juridique, traçabilité comptable probante et intégration des responsabilités sociétales.',
                'weight' => 2,
                'max_score' => 10,
                'position' => 4,
            ],
            [
                'name' => 'Investissabilité (#KPITAL_INVEST) & Impact socio-économique',
                'description' => 'Maturité de la Data Room, potentiel de levée de fonds, création d\'emplois durables et inclusion femmes/jeunes.',
                'weight' => 2,
                'max_score' => 10,
                'position' => 5,
            ],
        ];
    }

    public function getMneIndicators(): array
    {
        return [
            [
                'name' => 'Taux de participation féminine dans le programme',
                'definition' => 'Pourcentage de femmes porteurs de projets ou associées dirigeantes inscrites et actives (Objectif stratégique CUBE min 40%).',
                'formula' => 'Nombre de femmes accompagnées / Total des bénéficiaires * 100',
                'category' => 'INCLUSION',
                'target' => 40,
                'unit' => '%',
            ],
            [
                'name' => 'Taux de complétion et de rétention des startups',
                'definition' => 'Proportion des startups sélectionnées achevant l\'ensemble du cycle d\'incubation CUBE sans décrochage.',
                'formula' => 'Nombre de startups certifiées / Nombre de startups initialement enrôlées * 100',
                'category' => 'PORTEFEUILLE',
                'target' => 85,
                'unit' => '%',
            ],
            [
                'name' => 'Financements structurants mobilisés (#KPITAL_INVEST)',
                'definition' => 'Nombre de projets ayant sécurisé une levée de fonds, un prêt d\'honneur ou un investissement auprès du réseau Business Angels/partenaires.',
                'formula' => 'Cumul des startups financées au terme du programme',
                'category' => 'FINANCEMENT',
                'target' => 5,
                'unit' => 'financements',
            ],
            [
                'name' => 'Taux de conformité des Virtual Data Rooms (VDR)',
                'definition' => 'Pourcentage des projets disposant d\'un dossier d\'investissement complet avec toutes les pièces juridiques, financières et techniques validées.',
                'formula' => 'Data Rooms complétées à 100% / Total startups éligibles à la levée * 100',
                'category' => 'LIVRABLES',
                'target' => 90,
                'unit' => '%',
            ],
            [
                'name' => 'Volume d\'heures de formation certifiante & coaching',
                'definition' => 'Cumul des heures de formation professionnelle CUBE ACADEMY et des séances de mentorat individuel reçues par startup.',
                'formula' => 'Somme des heures de formation et coaching validées',
                'category' => 'MISE_EN_OEUVRE',
                'target' => 40,
                'unit' => 'heures',
            ],
            [
                'name' => 'Nombre de projets finalisés et lancés sur le marché',
                'definition' => 'Startups ayant finalisé leur solution MVP, initié leurs ventes effectives et structuré leurs contrats commerciaux.',
                'formula' => 'Nombre de projets opérationnels sur le marché à la clôture',
                'category' => 'PROGRESSION',
                'target' => 15,
                'unit' => 'projets',
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
