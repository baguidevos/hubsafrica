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

    public function getOfficialDriveResources(): array
    {
        return [
            [
                'title' => 'Formulaire de Diagnostic Initial d\'Entrée (Dossier Téléchargeable)',
                'description' => 'Dossier Drive contenant le formulaire d\'inscription et de diagnostic préalable pour auditer la maturité du projet.',
                'url' => 'https://drive.google.com/drive/folders/1g3R5o3Zrf2YF5I7657kiFvAvUyIuLQVg?usp=sharing',
                'type' => 'FOLDER',
                'is_official' => true,
            ],
            [
                'title' => 'Modèle de Plan de Travail & Plan d\'Accompagnement Personnalisé (PAS)',
                'description' => 'Dossier Drive avec le modèle de Plan de travail individualisé et le modèle de Plan d\'Accompagnement Personnalisé (PAS).',
                'url' => 'https://drive.google.com/drive/folders/1kbEFq9VVkwgKJOSLLCfxMy1jKrckmsFV?usp=sharing',
                'type' => 'FOLDER',
                'is_official' => true,
            ],
            [
                'title' => 'Documents Officiels de Formalisation de l\'Accompagnement',
                'description' => 'Dossier Drive des conventions d\'incubation, chartes d\'engagement et contrats types de la SAEI CUBE.',
                'url' => 'https://drive.google.com/drive/folders/1OTCCOJRxTTkcrNKdlvqxmtWO_MWIcjb7?usp=sharing',
                'type' => 'FOLDER',
                'is_official' => true,
            ],
            [
                'title' => 'Boîte à Outils Complète d\'Accompagnement Entrepreneuriat CUBE',
                'description' => 'Dossier Drive des différents outils, canevas et guides pratiques utilisés durant tout le déroulement de l\'accompagnement.',
                'url' => 'https://drive.google.com/drive/folders/1P5mhRplF599oTEFhHAFbrvroYOld2Y5B?usp=sharing',
                'type' => 'FOLDER',
                'is_official' => true,
            ],
            [
                'title' => 'Formulaire d\'Évaluation & Scoring de Projet (Par l\'Entrepreneur-e)',
                'description' => 'Tableur Google Sheets officiel d\'évaluation et de scoring avec justifications et feedback dans la section Feedback Entrepreneur-e.',
                'url' => 'https://docs.google.com/spreadsheets/d/1e_6lo4AR_82GcnBrgkhkz9y7nsoZvqx/edit?usp=sharing&ouid=101144735494555714130&rtpof=true&sd=true',
                'type' => 'SPREADSHEET',
                'is_official' => true,
            ],
            [
                'title' => 'Formulaire de Checking & Pièces de la Virtual Data Room (VDR)',
                'description' => 'Tableur Google Sheets de vérification rigoureuse de la présence et conformité de l\'ensemble des pièces du Data Room.',
                'url' => 'https://docs.google.com/spreadsheets/d/1J95rWQjx2aUIcci2AhrS_NAPHICbHIcW/edit?usp=sharing&ouid=101144735494555714130&rtpof=true&sd=true',
                'type' => 'SPREADSHEET',
                'is_official' => true,
            ],
            [
                'title' => 'Support de Formation Officiel Levée de Fonds #KPITAL_INVEST',
                'description' => 'Support de formation complet sur la levée de fonds, les Business Angels, la valorisation d\'entreprise et le pitch deck.',
                'url' => 'https://drive.google.com/file/d/1eHz9t2cMzULQchZuGVyuew2GCVwH2cEf/view?usp=sharing',
                'type' => 'DOCUMENT',
                'is_official' => true,
            ],
        ];
    }

    public function getDetailedGuide(): array
    {
        return [
            'overview' => [
                'institution' => 'C.U.B.E. (Centre Urbain de Business et d\'Entrepreneuriat), SAEI et holding immatriculée au Togo.',
                'entites_liees' => 'CUBE ACADEMY (centre de formation certifiante agréé par l\'État N°2025/101-102), programme #KPITAL_INVEST, PRIME CLASS.',
                'experience' => 'Plus de 10 ans d\'expertise, plus de 300 projets incubés, plus de 5 000 jeunes et femmes formés, réseau de +50 Business Angels.',
                'implantation' => 'Siège à Lomé, implantations régionales à Aného, Kpalimé et Kara, partenariats panafricains.',
                'vision_2028' => 'Devenir une référence africaine incontournable dans l\'accompagnement des startups innovantes, la formation aux compétences digitales et la mobilisation de financements structurants.',
            ],
            'valeurs' => [
                'Discipline' => 'Respect des engagements, rigueur dans l\'exécution, ponctualité, préparation minutieuse des séances et livraison à temps des activités prévues.',
                'Intégrité' => 'Honnêteté, transparence, responsabilité éthique dans la gestion des ressources, relations saines avec les partenaires et traçabilité des décisions.',
                'Excellence' => 'Recherche continue de qualité dans les services et livrables, rigueur d\'accompagnement et satisfaction maximale des bénéficiaires.',
                'Unicité' => 'Valorisation de chaque talent, idée et manière unique de contribuer, conception de solutions originales et adaptées aux réalités africaines.',
            ],
            'objectifs_strategiques' => [
                'Objectif 1 : Accompagner 200 entreprises et startups d\'ici 2028' => 'Sélectionner et suivre un volume croissant de projets à fort potentiel avec un haut taux de rétention et de concrétisation marché.',
                'Objectif 2 : Mobiliser au moins 20 financements structurants' => 'Préparer les projets à l\'investissement (#KPITAL_INVEST) et connecter les fondateurs aux Business Angels et fonds de capital.',
                'Objectif 3 : Étendre ses activités dans au moins 5 pays africains' => 'Déployer le modèle CUBE hors du Togo par des partenariats et des promotions pilotes régionales.',
                'Objectif 4 : Garantir au moins 40 % de femmes bénéficiaires' => 'Ciblage volontariste, aménagements d\'horaires, bourses et suivi désagrégé par genre.',
                'Objectif 5 : Structurer un fonds d\'investissement interne dédié' => 'Mécanisme d\'amorçage en capital pour accélérer les champions innovants locaux.',
                'Objectif 6 : Écosystème intégré Formation - Incubation - Financement' => 'Chaîne de valeur unique et fluide de la montée en compétences jusqu\'au passage à l\'échelle.',
            ],
            'pedagogical_phases' => [
                [
                    'phase' => 'Phase 1 : Diagnostic Initial, Sélection & Cadrage (PAS)',
                    'detail' => 'L\'accompagnement démarre par le remplissage du diagnostic en ligne (saeicube.com/diagnostic). Formulation du Plan de travail individualisé et du Plan d\'Accompagnement Personnalisé (PAS). Signature de la convention d\'incubation CUBE.',
                    'coach_tips' => 'Auditer avec rigueur les preuves fournies par le candidat et formaliser des objectifs réalistes et mesurables dans le PAS.',
                ],
                [
                    'phase' => 'Phase 2 : Préincubation — Validation du Business Model Complet (17 étapes)',
                    'detail' => 'Approche problème et Design Thinking. Segmentation fine de la clientèle, analyse approfondie de la concurrence directe et indirecte, promesse de valeur, spécifications du produit/service, canaux de distribution, relation client (CRM), flux de revenus, structure de coûts, KPIs et avantages compétitifs.',
                    'coach_tips' => 'Rappeler que partir d\'un problème humain réel sur le continent est la seule garantie de viabilité économique.',
                ],
                [
                    'phase' => 'Phase 3 : Incubation — Organisation, Gouvernance & Équipe',
                    'detail' => 'Renforcement de la posture et des compétences de l\'entrepreneur. Organigramme de l\'entreprise, gestion des ressources humaines (associés, employés clés, board), conception des fiches de poste et CVs, politique de Responsabilité Sociétale des Entreprises (RSE), propriété intellectuelle et gestion des stocks.',
                    'coach_tips' => 'Structurer dès l\'incubation les rôles pour éviter les conflits d\'associés et ancrer la culture d\'éthique et de discipline.',
                ],
                [
                    'phase' => 'Phase 4 : Incubation — Gestion Financière, Comptabilité & Juridique OHADA',
                    'detail' => 'Planification budgétaire, modèle financier prévisionnel sur 3 ans, gestion de trésorerie. Mise en place d\'une comptabilité probante avec traçabilité stricte des pièces justificatives. Choix de la forme juridique adaptée, conformité aux règles OHADA et rédaction des contrats d\'affaires types.',
                    'coach_tips' => 'Beaucoup d\'entreprises ratent des opportunités faute de traces comptables écrites ; exiger une rigueur documentaire absolue.',
                ],
                [
                    'phase' => 'Phase 5 : Accélération & #KPITAL_INVEST — Préparation à la Levée de Fonds',
                    'detail' => 'Auto-évaluation et scoring par l\'entrepreneur avec preuves tangibles. Constitution méthodique de la Virtual Data Room (VDR - espace sécurisé regroupant les pièces commerciales, juridiques, techniques et financières). Analyse des typologies d\'investisseurs (Business Angels vs Banques). Valorisation d\'entreprise (distinction essentielle valeur vs prix). Pitch Deck de 10 à 15 diapositives percutantes.',
                    'coach_tips' => 'Entraîner le porteur aux questions pièges de Due Diligence et adapter le discours au profil de l\'investisseur (impact vs rentabilité financière).',
                ],
                [
                    'phase' => 'Phase 6 : Connexion Investisseurs, Demo Day & Clôture',
                    'detail' => 'Présentations continues tout au long du cycle devant les investisseurs pour apprendre de ses erreurs. Rencontres avec le réseau de Business Angels (+50 membres). Négociation d\'accords d\'augmentation de capital. Grand événement de clôture en fin d\'année. Remise des certificats CUBE ACADEMY et feuille de route post-incubation.',
                    'coach_tips' => 'Préparer le closing des transactions et formaliser les contrats de suivi post-programme.',
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
