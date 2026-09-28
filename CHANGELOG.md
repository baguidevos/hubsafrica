# Changelog & Récapitulatif Technique — Hub Africa v2

Toutes les modifications notables apportées au projet **Hub Africa v2** (Stack Laravel 13 + Filament v5 + MySQL + Multi-tenant) sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

## [2.10.1] - 2026-09-28

### Documentation & Manuel d'Utilisation
- **Mise à Jour Intégrale du Manuel d'Utilisation & Guide Pratique (`GUIDE_UTILISATION_PLATEFORME_ACCOMPAGNEMENT.md`)** :
  - **Étape 3 (Candidatures, Vue Unifiée & Jurys)** : Documentation de la vue d'instruction mutualisée (SAE, Jurys Coachs, Candidats), stepper d'avancement à 4 étapes, actions rapides de délibération d'en-tête (*Noter*, *Accepter*, *Mettre en attente*, *Refuser*), portail dédié des coachs évaluateurs (`/coach/{tenant}/applications`), décision automatique selon le score et enrôlement direct.
  - **Étape 4 (Portefeuille, Paliers & Attribution Coach)** : Documentation complète du système de traçabilité des paliers :
    - Palier d'entrée initial verrouillé (`initial_level_id`)
    - Historique horodaté et calcul automatique de la durée passée dans chaque jalon
    - Gestionnaire relationnel « Historique des Paliers » (`LevelHistoriesRelationManager`) avec action assistée *« Promouvoir / Changer de palier »* et motif obligatoire
    - Palier de sortie et date d'achèvement (`exit_level_id`, `exited_at`) lors du passage en statut diplômé/sorti
    - Expérience porteur 360° avec règle d'unicité (1 porteur = 1 projet actif) et bloc d'avancement des paliers.
  - **Répertoire d'Actions par Cluster** : Enrichissement des clusters *Programmes & Sélection* et *Portefeuille Startups*, et ajout des sections dédiées au *Portail Coach* et au *Portail Entrepreneur*.
  - **Index des Captures d'Écran** : Actualisation des captures n°3 et n°4 intégrant la nouvelle ergonomie d'instruction et de progression.
- **Synchronisation du Guide Interactif Embarqué (`guide-page.blade.php`)** :
  - Mise à jour des étapes 3 et 4 du fil rouge BioAgri Tech pour refléter fidèlement les nouvelles interfaces.
  - Actualisation des fiches pratiques des Clusters 1 & 2 et des résumés de modules.

## [2.10.0] - 2026-09-28

### Ajouté (Added) & Amélioré (Improved)
- **Traçabilité Complète du Niveau d'Entrée, du Niveau de Sortie et Historique Horodaté des Paliers** :
  - Ajout des colonnes explicites `initial_level_id`, `exit_level_id` et `exited_at` sur la table `projects` pour des requêtes analytiques instantanées O(1).
  - Migration de rétrocompatibilité pour initialiser les projets existants à partir de leur historique le plus ancien ou niveau actuel.
  - Cycle de vie Eloquent automatisé (`booted` hooks dans `Project.php`) :
    - `creating` : initialisation automatique de `initial_level_id` et de `current_level_entered_at`.
    - `created` : création automatique de l'historique initial dans `project_level_histories`.
    - `updating` : détection des promotions/changements de `current_level_id`, fermeture horodatée de l'ancien palier (`ended_at`), création du nouveau palier avec traçabilité de l'auteur et motif, et création d'un événement `MILESTONE` dans la timeline du projet.
    - Clôture de parcours : passage en statut de sortie (`DIPLOME`, `SORTI`, `ABANDONNE`) fixant automatiquement `exit_level_id` et `exited_at`, clôturant les historiques ouverts et consignant l'événement de fin de parcours.
- **Gestionnaire de Relation Paliers SAE (`LevelHistoriesRelationManager`)** :
  - Intégration sur la fiche projet SAE d'une table dédiée traçant : Palier d'origine, Nouveau palier, Date d'entrée, Date de sortie, Durée calculée dans le niveau, Validateur, Motif & Contexte.
  - Action d'en-tête « Promouvoir / Changer de palier » avec modale de sélection du nouveau niveau et motif obligatoire.
- **Infolist SAE Enrichie (`ProjectResource`)** :
  - Affichage des badges : Palier d'entrée (Niveau initial), Palier actuel (avec date d'entrée), et Palier de sortie + date de fin de parcours (conditionnels si le projet est clôturé).
- **Interface Entrepreneur Haute Fidélité (`view-project.blade.php`)** :
  - Carte métrique actualisée affichant le Palier actuel et rappelant le niveau d'entrée.
  - Nouveau bloc immersif « Parcours d'accompagnement & Paliers » :
    - Stepper récapitulatif (Palier d'entrée → Palier actuel [→ Palier de sortie]).
    - Timeline chronologique stylisée de chaque palier franchi avec calcul automatique de durée, validateur et motif.
    - Section « Dates clés » enrichie avec la date et le palier de fin d'accompagnement.
- **Couverture de Tests Pest Automatisée** :
  - Création de `tests/Feature/ProjectLevelTrackingTest.php` validant l'ensemble du cycle de vie des paliers et de clôture.
  - 65 tests et 390 assertions validés à 100%.

## [2.9.0] - 2026-09-28

### Ajouté (Added) & Amélioré (Improved)
- **Unicité du Projet Entrepreneur & Expérience Singleton** :
  - Règle métier stricte : un entrepreneur ne peut désormais créer qu'un seul projet sur la plateforme (`canCreate` vérifiant l'inexistence de projet préalable).
  - Page `ListProjects` transformée en routeur transparent : auto-redirection immédiate vers `ViewProject` si le projet existe déjà, ou vers `CreateProject` si aucun projet n'a encore été créé.
  - Garde de sécurité dans `CreateProject::mount` pour empêcher toute création d'un second projet via URL directe.
  - Redirection automatique post-création et post-mise à jour vers la page de détails du projet (`ViewProject`).
- **Page de Détails du Projet Haute Fidélité (Portail Entrepreneur)** :
  - Nouvelle page `ViewProject` adossée à une vue Blade sur-mesure (`view-project.blade.php`).
  - Hero card avec dégradé émeraude, logo de la startup, nom et badge de statut dynamique avec icône officielle.
  - 4 métriques clés en cartes compactes : Secteur d'activité, Territoire/Région, Taille de l'équipe et Niveau/Jalon actuel.
  - Cartes d'offres : Problème identifié (rose/rouge) et Solution proposée (émeraude/vert) côte à côte, Proposition de valeur unique, Produits & Services, Historique de l'entreprise.
  - Volet d'accompagnement latéral : Cible & Marché visé, Promotion active, Coach assigné avec avatar, Décompte des séances et livrables, Dates clés d'intégration.
  - Pitch elevator mis en valeur sous forme de citation stylisée.
- **Refonte & Unification de la Vue Détails des Candidatures (3 Espaces : Entrepreneur, SAE, Coach)** :
  - Création d'une vue Blade adaptative partagée (`resources/views/filament/common/view-application.blade.php`) mutualisée entre les 3 portails :
    - Bannière d'en-tête dynamique avec dégradé thématique selon le statut du dossier (`SOUMISE`, `EN_EXAMEN`, `ACCEPTEE`, `EN_ATTENTE`, `REFUSEE`).
    - Note consolidée du jury sur 100 avec coloration conditionnelle ($\ge 75$ vert, $50-74$ orange, $< 50$ rouge).
    - Stepper visuel d'avancement à 4 jalons (*1. Soumission*, *2. Éligibilité*, *3. Jury*, *4. Délibération*) avec indicateurs complétés / en cours.
    - Encadré d'état contextuel avec message adapté au profil connecté (encouragement pour le porteur, consignes d'instruction pour les équipes SAE/Coach).
    - Carte récapitulative du projet lié avec bouton d'accès direct vers la fiche complète du projet (tenant-aware).
    - Sections Motivation du porteur, Besoins prioritaires, Réponses au formulaire de sélection spécifique, Grille multicritères avec barres de progression, et GED/documents joints.
- **Déploiement sur le Panneau SAE (`sae/{tenant}/programmes/applications/{record}`)** :
  - Nouvelle page `ViewApplication` remplaçant l'ancien slide-over avec actions de délibération dans le header :
    - 🎓 *Noter le dossier* : Modale interactive d'évaluation critère par critère avec coefficients pondérés.
    - 🏆 *Accepter* : Validation d'admission et enrôlement direct dans la cohorte.
    - ⏳ *Liste d'attente* : Mise en réserve du candidat.
    - ❌ *Refuser* : Rejet avec motif obligatoire.
    - ✏️ *Modifier* et 💡 *Fiche Projet*.
  - Carte profil du candidat dans la colonne latérale (coordonnées directes, localisation, formation).
  - Définition d'un `infolist` structuré sur `ApplicationResource` pour une consultation optimale.
- **Nouvelle Ressource Candidatures dans le Panneau Coach (`coach/{tenant}/applications`)** :
  - Création complète d'un espace *Candidatures & Jurys* pour les coaches (`ApplicationResource`, `ListApplications`, `ViewApplication`, `ApplicationsTable`).
  - Table avec filtres d'instruction (*À évaluer en priorité*) et action directe de notation.
  - Page `ViewApplication` dédiée permettant au coach d'évaluer le dossier comme membre de jury.
- **Correction et Compatibilité Filament v5 & PHP 8.4** :
  - Résolution de l'erreur `Class "Filament\Forms\Components\Actions\Action" not found` migré vers `Filament\Actions\Action`.
  - Résolution de l'erreur de route `filament.sae.resources.evaluation-forms.index` corrigée vers `filament.sae.impact.resources.evaluation-forms.index`.
  - Résolution de l'erreur PHP 8.4 FatalError liée à la redéclaration statique de la propriété `$view` dans les pages Filament `ViewRecord`.
  - Configuration de la largeur de contenu fluide `Width::Full` avec import strict sur les 4 PanelProviders.

## [2.8.1] - 2026-09-28

### Corrigé (Fixed) & Amélioré (Improved)
- **Correction d'Affichage du Score Jury (Bug Double Suffixe `56/100/100`)** :
  - Élimination du doublon de suffixe présent dans les colonnes Filament `score` (`ApplicationsTable` SAE et Entrepreneur, relation managers).
  - Formatage harmonisé avec code couleur dynamique selon la note (vert $\ge 75$, orange $\ge 50$, rouge $< 50$) et affichage propre `XX / 100` ou `Non notée`.
- **Bascule Automatique du Statut de Candidature selon la Note** :
  - Implémentation du système de décision automatique (`applyEvaluationDecision`) :
    - $\ge 75$ : `ACCEPTEE` (Avis favorable du jury).
    - $50 \text{ à } 74$ : `EN_ATTENTE` (Placée en liste d'attente).
    - $< 50$ : `REFUSEE` (Dossier non retenu).
  - Possibilité de dérogation manuelle dans la modale d'évaluation avec sélection du statut (Automatique ou Forcé).
  - Ajout d'actions rapides en 1 clic sur chaque candidature : *Accepter*, *Liste d'attente*, *Refuser avec motif*.
- **Propagation Automatique sur le Projet & Enrôlement en Promotion** :
  - Création/actualisation automatique de l'inscription du projet dans la promotion (`Enrollment`) dès l'acceptation de la candidature.
  - Bascule du statut du projet en `ACTIF` et alignement automatique sur le palier d'accompagnement (`level_id`) du programme avec historique `ProjectLevelHistory`.
  - Enregistrement automatique d'un jalon chronologique (`TimelineEvent` de type `MILESTONE` avec badge, score et cohorte) et d'un log d'audit (`ProjectUpdateLog`).
  - Annulation de l'enrôlement en cas de refus.
- **Visibilité 360° de la Promotion et du Score sur les Fiches Startups** :
  - Nouvelles colonnes *Cohorte* et *Note Jury* intégrées à la table principale des projets (`ProjectsTable`).
  - Fiche détaillée du projet (`ProjectResource::infolist`) enrichie avec la cohorte active et la dernière note du jury.
  - Nouveau relation manager *Candidatures & Évaluations* (`ApplicationsRelationManager`) attaché à la fiche projet pour consulter l'historique complet des dossiers et notations.
  - Ajout des relations Eloquent `latestEnrollment` et `latestApplication` sur `Project`, et des méthodes d'aide `isCoach()` et `isEntrepreneur()` sur `User`.
- **Qualité & Tests de Non-Régression** :
  - Suite de tests Pest `ApplicationEvaluationAndEnrollmentTest` (5 tests, 33 assertions).
  - Exécution complète des 62 tests du projet (367 assertions) au vert.
  - Formatage Pint validé.

## [2.8.0] - 2026-09-28

### Ajouté (Added) & Amélioré (Improved)
- **Architecture d'Accompagnement Hub Africa & Organisation par Clusters Métiers** :
  - Restructuration intégrale du panneau SAE en 4 Clusters fonctionnels fluides :
    - *🎓 Programmes & Sélection* : Fiche d'édition enrichie avec onglets combinés (*Informations*, *Phases & Jalons*, *Grille de Sélection & Jurys*, *Partenaires & Bailleurs*), gestion des promotions et candidatures.
    - *💼 Portefeuille Startups* : Fiches 360° des entreprises, profils des fondateurs et timeline chronologique.
    - *👥 Accompagnement & Mentorat* : Séances individuelles et ateliers collectifs, annuaire des coachs avec quota de charge max, invitations sécurisées, gestion des livrables et calendrier global.
    - *📊 Impact, Finance & Évaluation* : Indicateurs S&E avec relevés périodiques, financements & subventions, data room documentaire confidentielle et formulaires d'enquête.
  - Module Transverse *🎯 Pilotage & Décision* : Tableau de bord, Centre d'analyse graphique, Rapports & Exports certifiés pour bailleurs, Journal d'audit immuable et Guide d'utilisation interactif.
  - **Gestion Directe des Jalons & Phases de Cohorte** :
    - Nouveau menu direct `Jalons & Phases` permettant la visualisation centralisée et l'ajustement rapide des dates (`starts_on`, `ends_on`).
    - Action 1-clic *« Synchroniser depuis le programme »* pour calculer instantanément le rétro-planning complet de toute la promotion.
  - **Calendrier & Planning Global Unifié** (`CoachingCalendarPage`) :
    - Vue mensuelle interactive projetant en temps réel les séances de coaching, ateliers, jalons de promotions (lancements et échéances) et promotions.
    - Filtres par type d'événement, par cohorte et par coach avec modales de détail au clic.
  - **Gestion Électronique des Documents (GED) Polymorphe & Versionnée** :
    - Versionnage intelligent (`v1`, `v2`, `v3`) pour chaque livrable soumis avec archivage historique et téléchargement en un clic.
  - **Grille de Sélection & Notation Matricielle du Jury** :
    - Modale interactive d'évaluation critère par critère avec coefficients pondérés.
    - Calcul automatique de la note consolidée sur 100 avec code couleur dynamique (vert $\ge 75$, orange $\ge 50$, rouge $< 50$).
  - **Manuel d'Utilisation SAE & Page Guide In-App** :
    - Documentation complète `GUIDE_UTILISATION_PLATEFORME_ACCOMPAGNEMENT.md` structurée par cas pratique fil rouge (« BioAgri Tech »).
    - Page guide interactive intégrée au panel SAE (`/sae/{tenant}/pilotage/guide-page`) avec onglets, repères de captures d'écran et fiches réflexes.

- **Passerelle d'Enrôlement Direct & Gestion des Cohortes Ouvertes** :
  - **Passerelle d'accès direct 1-clic** (`/cohortes/{id}/rejoindre`) :
    - Accueil des candidats avec présentation de la cohorte et du programme.
    - Inscription express en 30 secondes pour les nouveaux candidats avec connexion immédiate et redirection automatique vers le formulaire pré-verrouillé sur la promotion.
    - Connexion directe pour les candidats disposant déjà d'un compte avec redirection automatique vers la même cohorte.
    - Redirection instantanée sans friction pour les utilisateurs déjà authentifiés.
  - **Génération & Partage Instantané du Lien de Promotion** :
    - Action rapide *« Lien direct »* avec modale et copie en 1 clic dans le presse-papier (`navigator.clipboard`) sur la table des cohortes et en tête de fiche d'édition.
  - **Mise en Valeur sur le Catalogue Public** :
    - Pastille verte animée `🟢 Cohorte Ouverte : [Nom]` sur les programmes ayant une promotion active.
    - Bouton d'action public transformé en `Rejoindre cohorte →` pointant directement sur la passerelle.
  - **Matrice des Présences de Promotion (Export Excel)** :
    - Export complet (`/sae/{tenant}/programmes/cohorts/{cohort}/export-attendance`) au format CSV/Excel croisant toutes les startups inscrites et toutes les séances de coaching de la promotion avec calcul automatique du taux d'assiduité.

- **Système de Templates de Formulaires de Candidature Standardisés & Personnalisés par SAE** :
  - **Modèle Officiel Standard Hub Africa** :
    - Initialisation automatique d'un template officiel panafricain (`is_standard = true`, `is_template = true`) comportant le socle commun de 13 champs (état civil, contact WhatsApp, profil porteur, startup, pitch synthétique, taille d'équipe, note de motivation, besoins d'accompagnement prioritaires, téléversement du pitch deck).
    - Protection stricte : interdiction de suppression ou de modification du modèle standard Hub Africa pour garantir l'uniformité des données pan-africaines.
  - **Formulaire Obligatoire à la Création de Cohorte** :
    - Champ `application_form_id` rendu obligatoire sur chaque cohorte (`CohortForm`), avec sélection dynamique entre le modèle standardisé et les templates propres à la structure, pré-sélectionné par défaut sur le standard Hub Africa.
  - **Création de Modèles Sur-Mesure par Structure (SAE)** :
    - Possibilité pour chaque SAE de créer ses propres templates personnalisés (`is_template = true`) ou de dupliquer en 1 clic le modèle officiel (`duplicateAsSaeTemplate`) pour y ajouter des questions d'éligibilité, des QCM ou des champs spécifiques.
    - Filtrage et badges d'usage (`Standard Hub Africa`, `Template Candidature`, `Enquête & Suivi`) dans `EvaluationFormResource`.
  - **Génération Dynamique dans le Wizard Public de Candidature** :
    - Détection automatique et rendu dynamique des questions complémentaires de la structure à l'Étape 3 du wizard Livewire (`ProgramApplicationWizard`).
    - Prise en charge des types de champs : texte libre, textarea, listes déroulantes (`select`), nombres, cases à cocher avec validation en temps réel.
  - **Persistance des Réponses Spécifiques & Consultation Jury** :
    - Enregistrement des réponses structurées dans la colonne `applications.answers` (JSON) et formatage soigné dans le commentaire de dossier pour les évaluateurs.
    - Section dédiée *« Réponses au questionnaire spécifique »* avec composant interactif `KeyValue` dans la fiche de candidature Filament SAE (`ApplicationForm`).
    - Colonne d'identification du modèle de formulaire utilisé (`Template`) dans la table des candidatures (`ApplicationsTable`).

- **Tests Fonctionnels & Robustesse** :
  - Suite de tests Pest complète validée à 100% (**56 tests passés, 322 assertions**).
  - Couverture dédiée pour le workflow complet des templates de candidature (`tests/Feature/CohortApplicationFormTemplateTest.php`).
  - Formatage conforme aux règles du projet via Laravel Pint (`vendor/bin/pint --format agent`).

## [2.7.0] - 2026-09-27

### Ajouté (Added) & Amélioré (Improved)
- **Action de Copie Immédiate dans la Notification d'Invitation de Coach** :
  - Intégration d'un bouton d'action **« Copier le lien »** (`copy_invitation_url`) avec icône et retour visuel dynamique (`✓ Lien copié !`) dans la notification Filament lors de la génération d'un lien d'invitation coach.
  - Implémentation via Alpine.js (`->alpineClickHandler(...)`) avec écriture directe dans le presse-papier (`navigator.clipboard.writeText`) et fallback universel (`execCommand('copy')`).
  - Ajout d'une seconde action **« Ouvrir »** ouvrant le lien d'invitation dans un nouvel onglet.
  - Centralisation de la génération d'URL et de notification dans [`CoachInvitation::makeInvitationNotification()`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Models/CoachInvitation.php) :
    - Déclenchée lors de la création d'une invitation depuis la page [`CreateCoachInvitation`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/CoachInvitations/Pages/CreateCoachInvitation.php).
    - Déclenchée lors de la création depuis le slideOver de [`ListCoachInvitations`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/CoachInvitations/Pages/ListCoachInvitations.php).
    - Déclenchée via l'action de ligne `copy_link` sur le tableau [`CoachInvitationsTable`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/CoachInvitations/Tables/CoachInvitationsTable.php).
    - Ajout de l'action directe « Générer un lien d'invitation » dans l'en-tête de la liste des coachs ([`ListCoaches`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/Coaches/Pages/ListCoaches.php)).
  - Ajout de la redirection de commodité `/register/coach?token=...` vers `/coach/invite/{token}`.
  - Tests Pest ajoutés et validés à 100% (34/34 tests passants).

## [2.6.0] - 2026-09-27

### Ajouté (Added)
- **Comptes de Test & Système de Connexion Instantanée 1-Clic** :
  - Création du document de référence [`COMPTES_TESTS.md`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/COMPTES_TESTS.md) recensant tous les comptes pré-configurés (Super Admin, Responsables SAE, Coachs/Mentors, Entrepreneurs) avec mot de passe universel `password`.
  - Contrôleur de démo dédié [`DevLoginController`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Http/Controllers/DevLoginController.php) gérant la connexion directe `/demo/login?as=...` et la déconnexion rapide `/demo/logout`.
  - Page interactive [`/comptes-test`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/resources/views/pages/comptes-test.blade.php) affichant les cartes de chaque profil avec copie en un clic des identifiants et accès directs aux tableaux de bord.
  - Intégration d'un Render Hook Filament (`PanelsRenderHook::AUTH_LOGIN_FORM_AFTER` dans [`quick-login.blade.php`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/resources/views/filament/hooks/quick-login.blade.php)) sur l'ensemble des formulaires de connexion (`/admin/login`, `/sae/login`, `/coach/login`, `/portal/login`) offrant :
    - Bouton « Remplir » (injection instantanée de l'email et du mot de passe dans le formulaire).
    - Bouton « 1-Clic » (authentification immédiate et redirection automatique vers le tableau de bord cible).
  - Ajout des accès rapides dans la barre de navigation et le menu « Espaces Dédiés » du site public.
  - Ajout de la redirection de commodité `/mentor` vers `/coach`.
  - Suite de tests enrichie et validée à 100% (32/32 tests passants).

## [2.5.0] - 2026-09-27

### Modifié (Changed) & Harmonisé (Aligned)
- **Palette Graphique Officielle Hub Africa Version 1** :
  - Extraction et intégration des codes couleur exacts de la Version 1 (`C:\Users\D3vOs\Projets\Laravel\Web\hub-africa`) :
    - Couleur primaire : `#F8B81D` (Jaune d'or / Gold Hub Africa)
    - Survol primaire : `#e5a510`
    - Couleur secondaire : `#2F3284` (Bleu indigo nuit profond / Navy)
    - Accent chaleureux : `#F9B104` (Ambre)
  - Configuration centralisée dans `config/hubafrica.php` et déclaration des variables de design system dans `resources/css/app.css` (Tailwind `@theme`).
  - Déclinaison harmonieuse sur l'ensemble des pages publiques (Accueil, Catalogue des programmes, Fiche programme, Annuaire SAE, Fiche SAE, Parcours d'orientation, et Assistant de candidature multi-étapes).
- **Intégration du Logo Officiel & Partenaire de la Version 1** :
  - Déploiement du véritable logo horizontal Hub Africa issu de la v1 (`koworkers.png` — "Hub" doré `#F8B81D`, "Africa" noir avec point doré sur le 'i') dans le header et le footer.
  - Déploiement de l'emblème officiel partenaire `iyba-seed.png` dans la grille des partenaires institutionnels.
- **Remplacement des Favicons par ceux de la Version 1** :
  - Intégration dans `public/` de `favicon.ico`, `favicon.svg`, et `apple-touch-icon.png` officiels de Hub Africa v1.
  - Référencement complet dans le layout public (`<x-layouts.public>`).
- **Tests & Robustesse** :
  - Suite de tests Pest validée à 100% (25/25 tests passants).

## [2.4.0] - 2026-09-27

### Modifié (Changed) & Corrigé (Fixed)
- **Suppression intégrale des mentions abusives de "CUBE"** :
  - Clarification du rôle de CUBE en tant que Structure d'Accompagnement à l'Entrepreneuriat (SAE) partenaire et non comme cadre méthodologique global de la plateforme.
  - Remplacement systématique dans les vues publiques, filtres, formulaires et panneaux Filament (Admin, SAE, Entrepreneur, Coach) par les désignations génériques professionnelles ("Suivi & Évaluation", "S&E", "Phase / Niveau", "Livrables Méthodologiques").
  - Nettoyage des libellés dans `EvaluationFormResource`, `ReportsPage`, `PerformanceRatesWidget`, formulaires de jalons, catalogues et widgets.
- **Humanisation & Épuration du Design Public** :
  - Élimination des artifices visuels génériques d'IA (émoticônes superflus type 🚀, halos lumineux néon, dégradés de texte multicolores).
  - Adoption d'une direction artistique sobre, chaleureuse, épurée et authentique, valorisant la réalité du terrain entrepreneurial africain.
  - Ajout de témoignages réels et ancrés d'entrepreneurs et de coordinateurs d'incubateurs (ex. Cotonou, Dakar).
  - Refonte de la section méthode avec un parcours clair en 4 étapes (« De l'idée au marché »).
- **Correction de la Navbar sur Écrans d'Ordinateurs Portables (1024px – 1366px)** :
  - Résolution du problème de débordement horizontal sur les résolutions intermédiaires (1366x768 et tablettes paysage).
  - Masquage intelligent du sous-titre de logo sur les écrans inférieurs à `xl`.
  - Passage des liens de navigation avec espacement optimisé (`gap-6`) et boutons d'action compacts, maintenant une largeur totale sous les 650px.
- **Suppression de la mention "v2" sur l'espace public** :
  - Harmonisation de la marque publique sous le nom unique **Hub Africa**.
  - Retrait du badge "v2" dans la barre de navigation, les en-têtes, les métadonnées SEO et le pied de page public.

## [2.3.0] - 2026-09-27

### Ajouté (Added)
- **Portail Public Institutionnel & Panafricain (Blade + Tailwind v4 + Livewire 4)** :
  - **Identité de marque & Alignement CUBE S&E** :
    - Design institutionnel haut standing répondant aux exigences des bailleurs et partenaires (CUBE S&E, IYBA-SEED, AFD, Enabel, BAD, Union Européenne).
    - Métriques d'impact en temps réel sur la page d'accueil (`/`) : nombre de startups accompagnées, structures SAE en réseau, heures de mentorat certifiées et financements mobilisés (en FCFA).
    - Barre supérieure officielle ("République du Bénin / Initiative Panafricaine CUBE S&E"), logos partenaires, menu de navigation contextuel et accès direct aux portails (`/portal`, `/mentor`, `/sae`).
  - **Catalogue Réactif des Programmes d'Incubation & Accélération (`/programmes`)** :
    - Moteur de recherche et de filtrage dynamique par mots-clés, SAE d'accueil, niveau de maturité CUBE (Idéation, Prototypage, Amorçage, Croissance) et statut d'enrôlement.
    - Fiche détaillée de programme (`/programmes/{id}`) : objectifs, critères d'éligibilité, dotations, SAE opératrice, durée et bouton d'action vers la candidature.
  - **Annuaire des Structures d'Accompagnement (SAE) (`/structures`, `/structures/{id}`)** :
    - Cartographie des incubateurs et accélérateurs affiliés avec programmes actifs et coordonnées officielles.
  - **Orientation & Aiguillage des Profils (`/rejoindre`, `/account-type-selection`)** :
    - Page de sélection de parcours guidé (Entrepreneur, Mentor/Coach, Structure d'Accompagnement SAE).
  - **Assistant de Candidature Multi-étapes (`/programmes/{id}/candidater`)** :
    - Composant Livewire réactif `ProgramApplicationWizard` en 3 étapes :
      - *Étape 1* : Identité entrepreneuriale & création de compte (nom, prénom, email, téléphone, mot de passe).
      - *Étape 2* : Profil de la startup / projet (nom, secteur d'activité, stade de maturité, description, ville).
      - *Étape 3* : Motivations & téléversement du pitch deck / dossier de présentation.
    - Création transactionnelle automatique de l'utilisateur (`User`), profil entrepreneur (`Entrepreneur` rattaché à la SAE), projet (`Project`), candidature (`Application` au statut `SOUMISE`) et événement d'historique (`TimelineEvent`).
    - Authentification automatique immédiate après soumission avec redirection vers le portail entrepreneur (`/portal/{tenant}`).

- **Optimisations Avancées Livewire 4** :
  - **Directive `@placeholder`** : Chargement fluide avec squelettes d'animation SVG institutionnels pour le catalogue de programmes.
  - **Attribut `#[Js]`** : Réinitialisation instantanée des filtres côté client sans aller-retour serveur (`resetFilters()`).
  - **Attribut `#[Json]`** : Endpoint réactif ultra-léger pour consultation asynchrone des métriques d'un programme (`getProgramQuickStats($id)`).

- **Référencement & Métadonnées SEO avec `laravel/head`** :
  - Intégration du package officiel `laravel/head`.
  - Configuration globale dans `AppServiceProvider.php` via `Head::defaults()` (OpenGraph, Twitter Cards, balises canoniques, séparateurs de titres dynamiques).
  - En-têtes personnalisés dynamiques sur chaque page publique (`HomeController`, `ProgramPublicController`, `StructurePublicController`).

- **Moteur de Thème & Palette Configurable (`config/hubafrica.php`)** :
  - Fichier de configuration centralisé `config/hubafrica.php` gérant l'identité, les contacts, les métadonnées SEO et la charte graphique.
  - Système de variables CSS dynamiques (`--ha-primary`, `--ha-secondary`, `--ha-accent`, `--ha-surface`) injectées dans le layout et intégrées aux tokens `@theme` de Tailwind CSS v4 (`--color-brand-primary`, etc.).
  - Personnalisation immédiate des couleurs via variables d'environnement `.env` sans recompilation des assets.

- **Suite de Tests Fonctionnels Pest (`tests/Feature/PublicFrontendAndApplicationTest.php`)** :
  - 5 tests complets validant la page d'accueil, le catalogue Livewire, la fiche détaillée, le flux de candidature de bout en bout et l'annuaire des structures (49 assertions).

### Corrigé (Fixed)
- **Suppression du rendu textuel brut de `@placeholder` dans le catalogue** :
  - Dans Livewire 4, la directive `@placeholder` est exclusivement dédiée au compilateur d'îlots (`@island(lazy: true)`). Placée en tête d'une vue de composant standard, elle était renvoyée telle quelle sous forme de texte brut dans le DOM du navigateur.
  - Retrait des balises `@placeholder` / `@endplaceholder` de la vue Blade [`programmes-catalog.blade.php`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/resources/views/livewire/public/programmes-catalog.blade.php).
  - Implémentation de la méthode officielle Livewire `public function placeholder(): string` sur le composant [`ProgrammesCatalog.php`](file:///C:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Livewire/Public/ProgrammesCatalog.php) pour le lazy loading natif.
  - Intégration d'un squelette animé fluide via `wire:loading.delay.shorter` et `wire:loading.remove` lors des recherches et filtrages interactifs.
  - Ajout d'assertions négatives (`assertDontSee('@placeholder')`) dans les tests fonctionnels.
- **Résolution de la colonne `order_index` dans `ProgrammesCatalog`** :
  - Correction de l'erreur SQL `1054 Unknown column 'order_index' in 'ORDER BY'` sur la table `levels`.
  - Alignement avec la colonne réelle de la base de données `order` (`orderBy('order')`).
  - Scoping automatique des niveaux par SAE lorsque le filtre SAE est sélectionné.
  - Ajout d'un accesseur `$level->order_index` sur le modèle `Level` pour rétrocompatibilité.

---

## [2.2.1] - 2026-09-27

### Corrigé (Fixed)
- **Résolution du cast Enum dans `EntrepreneurStatsOverviewWidget`** :
  - Correction de l'erreur `Object of class App\Enums\ProjectStatus could not be converted to string` survenue lors de la concaténation directe du statut de projet.
  - Conversion sécurisée du BackedEnum `ProjectStatus` via `->getLabel()`, avec application dynamique de la couleur (`getColor()`) et de l'icône (`getIcon()`) associées au statut du projet.
  - Ajout d'un test fonctionnel Pest (`entrepreneur stats overview widget renders properly with ProjectStatus enum`) garantissant la robustesse du rendu.
- **Résolution de la colonne `max_participants` dans `PerformanceRatesWidget`** :
  - Correction de l'erreur `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'max_participants' in 'SELECT'` lors du calcul du taux de remplissage.
  - Remplacement de `Cohort::sum('max_participants')` par la colonne réelle du schéma `Cohort::sum('capacity')`.
  - Ajout d'un accesseur rétrocompatible `$cohort->max_participants` sur le modèle `Cohort`.
  - Prise en charge élargie des statuts de livrables (`VALIDEE`/`VALIDE`, `REJETEE`/`NON_VALIDE`/`CORRECTION_DEMANDEE`).
  - Ajout d'un test fonctionnel Pest (`sae performance rates widget computes formulas and renders without SQL errors`).
- **Résolution de route `filament.entrepreneur.pages.fill-questionnaire`** :
  - Normalisation du nom de route pour le widget de questionnaires en attente de l'entrepreneur.

### Amélioré (Improved)
- **Navigation SPA & Performance** :
  - Activation de `->spa(hasPrefetching: true)` sur `EntrepreneurPanelProvider` et `SaePanelProvider` pour une navigation instantanée sans rechargement de page.

---

## [2.2.0] - 2026-09-27

### Ajouté (Added)
- **Système d'Affectation des Coachs aux Projets (`CoachAssignment`)** :
  - **Gestion bidirectionnelle depuis le panel SAE** :
    - *Depuis la fiche Coach (`CoachResource`)* : Ajout du relation manager `AssignedProjectsRelationManager` et de l'action rapide de tableau `Affecter à des projets` avec suivi en temps réel de la charge active (ex. `2 / 5 startups`).
    - *Depuis la fiche Projet (`ProjectResource`)* : Ajout de l'action `Assigner un coach` dans `CoachAssignmentsRelationManager` avec affichage des spécialités des mentors et de leur disponibilité.
  - **Résolution automatique du contexte CUBE** : Attribution automatique du `program_id` et du `cohort_id` basés sur l'enrôlement du projet.
  - **Notifications & Traçabilité** :
    - Notification in-app au coach dans son portail `/mentor/{tenant}`.
    - Notification in-app à l'entrepreneur dans son portail `/portal/{tenant}`.
    - Journalisation automatique d'un événement `TimelineEvent` sur le parcours du projet.
  - **Cycle de vie de l'affectation** : Clôture (`Mettre fin`) ou révocation avec horodatage `ended_at`.
- **Système complet d'Évaluation & Enquêtes dynamiques par Programme (CUBE S&E)** :
  - **Typologie BackedEnum `FormType`** (`app/Enums/FormType.php`) :
    - `BASELINE` : Diagnostic initial / Entrée (cadrage, maturité de départ)
    - `DIAGNOSTIC` : Diagnostic de maturité intermédiaire
    - `SATISFACTION` : Enquête de satisfaction (mi-parcours ou fin d'atelier)
    - `ENDLINE` : Bilan final / Sortie de programme
    - `POST_PROGRAMME` : Suivi d'impact à 6-12 mois (emplois durables, CA)
    - `CANDIDATURE` : Formulaire de candidature
    - `ABANDON` : Questionnaire de sortie prématurée
    - `COLLECTE_DONNEES` : Collecte périodique d'indicateurs
    - `AUTRE` : Autres enquêtes personnalisées
    - Implémentation complète de `HasLabel`, `HasColor`, `HasIcon` de Filament.
  - **Statut de Déblocage BackedEnum `FormUnlockStatus`** (`app/Enums/FormUnlockStatus.php`) :
    - `EN_ATTENTE` (warning), `SOUMIS` (success), `EN_RETARD` (danger).
  - **Extension du modèle `Form`** (`app/Models/Form.php`) :
    - Hérite du modèle de Packstub Form Builder et configure les relations CUBE : `program()`, `targetLevel()`, `sae()`, `unlocks()`.
    - Méthodes calculées : `totalUnlocksCount()`, `completedUnlocksCount()`, `pendingUnlocksCount()`, `completionRate()`.
  - **Mise à niveau du modèle `FormUnlock`** (`app/Models/FormUnlock.php`) :
    - Ajout des attributs `status`, `completed_at`, `submission_id`.
    - Méthodes d'état : `isOverdue()`, `isCompleted()`.
    - Scopes Eloquent : `scopePending()`, `scopeCompleted()`.
  - **Ressource SAE `EvaluationFormResource`** (`app/Filament/Sae/Resources/EvaluationForms/EvaluationFormResource.php`) :
    - Onglet dédié **Cadrage CUBE S&E** (Programme rattaché, Type, Jalon de niveau, Obligation).
    - Tableau avec colonnes enrichies : Badge Type, Programme associé, Assignés, Soumissions, Taux de complétion dynamique (%), Statut actif et requis.
    - Action rapide **Assigner** sur chaque ligne de tableau.
    - Gestionnaire de relations `UnlocksRelationManager` pour suivre les assignations par cohorte, relancer les retardataires en 1 clic et consulter les réponses soumises.
  - **Dispositif multi-canal d'information & sensibilisation pour l'Entrepreneur** :
    1. **Badge dynamique de navigation** (`QuestionnairesPage`) : Compteur en temps réel des questionnaires en attente, passant en rouge (`danger`) en cas de retard d'échéance.
    2. **Widget d'alerte sur Dashboard** (`EntrepreneurPendingSurveysWidget`) : Cartes d'action prioritaires en haut du tableau de bord avec délai, consigne du mentor et bouton direct *Remplir*.
    3. **Notifications In-App** : Cloche de notification Filament avec liens directs vers les questionnaires.
    4. **Page de remplissage dédiée** (`FillQuestionnairePage`) : Intégration interactive Livewire du Form Builder avec protection par profil entrepreneur et récapitulatif des réponses après soumission.
  - **Automatisation & Cycle de vie de soumission (`HandleEvaluationSubmission`)** :
    - Écoute de l'événement `SubmissionReceived`.
    - Mise à jour automatique de `FormUnlock` vers `SOUMIS` avec `completed_at` et `submission_id`.
    - Journalisation automatique d'un événement `TimelineEvent` sur la chronologie du projet.
    - Notifications croisées (confirmation pour l'entrepreneur et alerte pour l'équipe SAE).
  - **Tests automatisés de bout en bout** (`tests/Feature/EvaluationFormSystemTest.php`) :
    - 4 tests complets validant le cycle complet : typologie, calcul des retards, badge de navigation, widget dashboard, listener et timeline.
    - Suite globale : **16 tests passés avec succès, 80 assertions (100% OK)**.
  - **Données de démonstration (Seeders)** :
    - 3 formulaires prêts à l'emploi (*Baseline*, *Satisfaction*, *Endline*) injectés et assignés à *Ousmane Fall* (`ousmane.fall@ecobat.sn`).

---

## [2.1.0] - 2026-09-26

### Ajouté (Added)
- **Portails & Vues Alignés sur l'expérience Next.js** :
  - **Tiroirs latéraux `slideOver()`** sur l'ensemble des formulaires et fiches de consultation :
    - `FundingRecords` (Cadrage, Montants en FCFA, Justificatifs, Statuts).
    - `CoachingSessions` (Objectifs, Compte-rendu, Actions à mener, Types, Statuts).
    - `DeliverableSubmissions` & `DeliverableDefinitions` (Dépôt de fichiers, Seuil de validation, Modal d'évaluation inline).
    - `Cohorts` & `Programs` (Capacité, Dates, Objectifs pédagogiques).
    - `Coaches` & `CoachInvitations` (Spécialités, Liens d'invitation avec token).
    - `Indicators` (8 catégories CUBE, Cibles et Réalisés avec unités %, FCFA, Nb).
    - `Projects`, `Entrepreneurs`, `Resources`, `TimelineEvents`, `DataRoomDocuments`.
  - **Architecture BackedEnums stricte** :
    - Remplacement de toutes les chaînes en dur par des enums typés avec contrats `HasLabel`, `HasColor`, `HasIcon` (`ProgramStatus`, `CohortStatus`, `ApplicationStatus`, `CoachStatus`, `CoachInvitationStatus`, `CoachingSessionStatus`, `CoachingSessionType`, `EntrepreneurStatus`, `FundingStatus`, `FundingType`, `IndicatorStatus`, `IndicatorCategory`, `ProjectStatus`, `Gender`).
  - **Sécurisation Multi-Tenant Filament v5** :
    - Isolation stricte des données par `Sae` via `TenantScope` et le trait `BelongsToTenant`.
    - Règle métier : Un utilisateur ne peut posséder qu'une seule structure SAE (`canCreateTenants()`).

---

## [2.0.0] - 2026-09-25

### Ajouté (Added)
- **Socle initial Hub Africa v2 sous Laravel 13 & Filament v5** :
  - 3 Panels configurés :
    - SAE Admin : `/sae/{tenant}`
    - Coach / Mentor : `/mentor/{tenant}`
    - Entrepreneur : `/portal/{tenant}`
  - Système d'invitation des coachs avec inscription autonome par lien sécurisé (`/coach/invite/{token}`).
  - Intégration de `spatie/laravel-permission` et `bezhansalleh/filament-shield`.
  - Intégration du générateur de formulaires `packstub/filament-form-builder`.
  - Base de données MySQL avec migrations et seeders complets de l'écosystème CUBE S&E.

---

## Identifiants de Test (Développement & Démonstration)

| Rôle | URL de connexion | Email | Mot de passe | Contexte / Tenant |
| :--- | :--- | :--- | :--- | :--- |
| **Super Administrateur** | `/admin` | `admin@hubafrica.org` | `password` | Plateforme globale |
| **Responsable SAE** | `/sae/teranga-africa` | `fatou.diop@teranga.sn` | `password` | Teranga Africa Hub |
| **Coach / Mentor** | `/mentor/teranga-africa` | `marie.sarr@coach.sn` | `password` | Teranga Africa Hub |
| **Entrepreneur** | `/portal/teranga-africa` | `ousmane.fall@ecobat.sn` | `password` | EcoBat Sénégal |
