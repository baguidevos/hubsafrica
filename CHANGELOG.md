# Changelog & Récapitulatif Technique — Hub Africa v2

Toutes les modifications notables apportées au projet **Hub Africa v2** (Stack Laravel 13 + Filament v5 + MySQL + Multi-tenant) sont documentées dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/).

---

## [2.2.0] - 2026-09-27

### Ajouté (Added)
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
