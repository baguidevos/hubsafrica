# Changelog & Récapitulatif Technique — Hub Africa v2

Toutes les modifications notables apportées au projet **Hub Africa v2** (Stack Laravel 13 + Filament v5 + MySQL + Multi-tenant) sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

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
