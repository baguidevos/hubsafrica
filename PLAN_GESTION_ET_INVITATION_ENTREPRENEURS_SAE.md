# Plan d'implémentation : Gestion & Invitation des Entrepreneurs par les SAE

Ce document détaille l'architecture et les étapes de mise en œuvre pour permettre aux Structures d'Accompagnement à l'Entrepreneuriat (**SAE**) de :
1. **Consulter exhaustivement la liste de leurs entrepreneurs** (avec leurs cohortes actuelles, projets et indicateurs).
2. **Inviter des entrepreneurs à s'inscrire** sur la plateforme via un lien sécurisé et par email.
3. **Relier ou non l'entrepreneur à une cohorte en cours**, particulièrement utile lorsqu'une SAE adopte Hubs Africa alors qu'un programme d'accompagnement est déjà en cours d'exécution.
4. **Rattacher à tout moment un entrepreneur existant** à une cohorte active.

---

## 1. Contexte & Enjeux Fonctionnels

Dans le cycle de vie de la plateforme, deux cas d'usage coexistent :
- **Parcours standard par appel à candidatures** : L'entrepreneur postule publiquement, son dossier est évalué, puis il est admis dans une cohorte.
- **Parcours d'intégration directe ("Fast-Track" / Process en cours)** : La SAE accompagne déjà des entrepreneurs avant d'utiliser Hubs Africa ou recrute des lauréats hors plateforme. La SAE souhaite inviter ces porteurs de projets directement sans leur imposer tout le processus de sélection, et optionnellement les affecter immédiatement à la cohorte en cours.

---

## 2. Architecture Technique

### 2.1 Modèle de Données & Base

- **Migration & Table `entrepreneur_invitations`** :
  - `id`
  - `sae_id` (foreignId vers `saes`, scoped tenant)
  - `email` (string, requis)
  - `first_name` (string, nullable)
  - `last_name` (string, nullable)
  - `phone` (string, nullable)
  - `project_name` (string, nullable — si la SAE connaît déjà l'intitulé du projet)
  - `cohort_id` (foreignId vers `cohorts`, nullable — cohorte cible facultative)
  - `token` (string 64 unique)
  - `status` (`PENDING`, `ACCEPTED`, `EXPIRED`, `REVOKED`)
  - `message` (text nullable — note ou mot d'accueil personnalisé)
  - `expires_at` (datetime, ex: 14 jours par défaut)
  - `accepted_at` (datetime, nullable)
  - `created_by` (foreignId vers `users`, nullable)
  - Timestamps & index (`sae_id`, `status`)

- **Enum `EntrepreneurInvitationStatus`** :
  - `PENDING` (En attente - warning)
  - `ACCEPTED` (Acceptée - success)
  - `EXPIRED` (Expirée - gray)
  - `REVOKED` (Révoquée - danger)

- **Modèle Eloquent `EntrepreneurInvitation`** :
  - Trait `BelongsToTenant`
  - Relations : `sae()`, `cohort()`, `creator()`
  - Helpers : `isPending()`, `isExpired()`, `getInvitationUrl()`, `makeInvitationNotification()` (avec bouton copier le lien)

- **Ajustement table `enrollments`** :
  - S'assurer que `project_id` peut être nul temporairement ou associer systématiquement le projet initial créé lors de l'onboarding.

---

### 2.2 Parcours & Écrans Côté SAE (Espace Filament)

1. **Ressource `EntrepreneurResource` (enrichissement de la vue existante)** :
   - **Colonnes du tableau** :
     - Nom complet, email, téléphone, photo/avatar.
     - **Badge Cohorte en cours** : affiche la promotion active ou « Hors cohorte ».
     - **Projet(s)** : Nom du projet principal et secteur.
     - Statut du dossier d'accompagnement (`ACTIVE`, `DIPLOME`, `SUSPENDU`, etc.).
     - Date d'intégration.
   - **Filtres** :
     - Filtre par Cohorte (toutes, cohorte spécifique, sans cohorte).
     - Filtre par Statut d'accompagnement.
     - Filtre par Genre & Territoire.
   - **Actions de ligne / tableau** :
     - **Action "Rattacher à une cohorte"** : Modale permettant d'inscrire directement un entrepreneur dans une cohorte active de la SAE sans réinvitation.
     - Action d'en-tête : Bouton direct **« Inviter un entrepreneur »**.

2. **Ressource `EntrepreneurInvitationResource`** :
   - Intégrée dans le cluster `PortefeuilleCluster` aux côtés de `EntrepreneurResource`.
   - Table listant l'historique des invitations envoyées (Email, Destinataire, Cohorte cible, Statut, Date d'envoi/expiration).
   - Actions :
     - **Copier le lien d'invitation** (avec notification Filament et copie presse-papier automatique).
     - **Renvoyer l'email d'invitation**.
     - **Révoquer l'invitation**.
   - Formulaire d'envoi d'invitation :
     - Email (obligatoire), Prénom, Nom, Téléphone.
     - **Sélection de Cohorte** (optionnelle : select des cohortes `EN_COURS` ou `PLANIFIEE` de la SAE).
     - Nom du projet pressenti (optionnel).
     - Message personnalisé (optionnel).

---

### 2.3 Parcours Public d'Inscription & Onboarding

1. **Lien d'invitation sécurisé** : `/entrepreneur/invite/{token}`
2. **Contrôleur `EntrepreneurInvitationController`** :
   - `show($token)` : Vérification de la validité de l'invitation (statut `PENDING`, non expirée).
   - Présentation d'une page élégante aux couleurs de la SAE invitante et du Hubs Africa :
     - Rappel de l'invitation par la SAE.
     - Mention de la promotion/cohorte si renseignée (ex: *« Promotion Accélération 2026 - Cohorte 1 »*).
     - Formulaire prérempli : Prénom, Nom, Email, Téléphone.
     - Définition du mot de passe sécurisé.
     - Renseignement du projet (Nom du projet, secteur d'activité, description synthétique).
3. **Traitement `register($token)` en transaction de base de données** :
   - Création / Mise à jour du compte `User` (avec attribution du rôle Spatie `entrepreneur` dans le tenant).
   - Création de la fiche `Entrepreneur` (rattachée à la SAE et à l'utilisateur).
   - Création de la fiche `Project` de l'entrepreneur.
   - **Si une cohorte a été spécifiée dans l'invitation** :
     - Création immédiate de l'`Enrollment` (statut `ACTIF`, cohorte, projet, entrepreneur).
   - Clôture de l'invitation (`status = ACCEPTED`, `accepted_at = now()`).
   - Connexion automatique de l'utilisateur (`Auth::login($user)`).
   - Redirection directe vers le tableau de bord entrepreneur (`/entrepreneur`).

---

### 2.4 Notifications & Emails

- **Mailable `EntrepreneurDirectInvitationMail`** :
  - Sujet : *« [Nom SAE] vous invite à rejoindre Hubs Africa »* (ou mention de la promotion si présente).
  - Contenu : Logo de la SAE, message personnalisé, lien d'activation sécurisé valable 14 jours.
- Envoi asynchrone via file d'attente (Queue).

---

## 3. Checklist des Étapes de Réalisation

Suivons ces étapes ensemble de manière progressive et vérifiée :

- [x] **Étape 1 : Base de données & Modèle**
  - [x] Créer la migration `create_entrepreneur_invitations_table`.
  - [x] Créer l'enum `App\Enums\EntrepreneurInvitationStatus`.
  - [x] Créer le modèle `App\Models\EntrepreneurInvitation` avec relations et helpers de lien.
  - [x] Assouplir la contrainte `project_id` sur la table `enrollments` (migration exécutée).
  - [x] Exécuter les migrations (`php artisan migrate`).

- [x] **Étape 2 : Communication & Emailing**
  - [x] Créer le mailable `App\Mail\EntrepreneurDirectInvitationMail`.
  - [x] Créer la vue Blade pour l'email responsive (`resources/views/emails/entrepreneur-invitation.blade.php`).

- [x] **Étape 3 : Interface SAE — Gestion des Invitations**
  - [x] Créer `App\Filament\Sae\Resources\EntrepreneurInvitations\EntrepreneurInvitationResource`.
  - [x] Configurer le formulaire d'invitation avec sélection dynamique des cohortes en cours/planifiées de la SAE.
  - [x] Configurer le tableau des invitations avec filtres, badge de statut, bouton de copie de lien et action d'envoi d'email.

- [x] **Étape 4 : Interface SAE — Amélioration de la Liste des Entrepreneurs**
  - [x] Enrichir `App\Filament\Sae\Resources\Entrepreneurs\Tables\EntrepreneursTable` :
    - [x] Ajouter la colonne des cohortes / promotion rattachée.
    - [x] Ajouter la colonne du projet principal.
    - [x] Ajouter le filtre par Cohorte.
    - [x] Ajouter l'action directe de ligne « Assigner à une cohorte » (modale d'assignation en direct).
    - [x] Ajouter l'action d'en-tête « Inviter un entrepreneur » pour un accès direct en 1 clic.

- [x] **Étape 5 : Portail Public d'Acceptation & Activation**
  - [x] Créer `App\Http\Controllers\EntrepreneurInvitationController`.
  - [x] Créer la vue publique Blade `resources/views/auth/entrepreneur-invite.blade.php`.
  - [x] Créer la vue d'erreur d'invitation `resources/views/auth/entrepreneur-invite-invalid.blade.php`.
  - [x] Déclarer les routes dans `routes/web.php` (`/entrepreneur/invite/{token}`).
  - [x] Gérer l'onboarding complet : création User + Entrepreneur + Project + Enrollment conditionnel + connexion automatique.

- [x] **Étape 6 : Tests & Validation**
  - [x] Créer un test Pest validant le cycle d'invitation avec cohorte.
  - [x] Créer un test Pest validant le cycle d'invitation sans cohorte.
  - [x] Valider l'assignation directe depuis le tableau SAE.
  - [x] Exécuter Laravel Pint pour respecter les standards de code.

---

## 4. Points d'Attention & Cas Limites

1. **Email déjà existant dans la plateforme** :
   - Si l'entrepreneur possède déjà un compte User sur Hubs Africa (ex: inscrit sur un autre programme ou dans une autre SAE), l'invitation lui permet de se connecter avec son mot de passe existant pour lier son profil à la nouvelle SAE et à la nouvelle cohorte sans doublon de compte.
2. **Cohorte clôturée entre l'envoi et l'acceptation** :
   - Si la cohorte sélectionnée a été clôturée avant que l'entrepreneur n'active son compte, l'inscription de l'entrepreneur est quand même validée dans la SAE, et une alerte l'oriente vers la SAE.
3. **Sécurité du Token** :
   - Token cryptographiquement aléatoire de 64 caractères, expiration paramétrable (14 jours), invalidation immédiate après utilisation (`ACCEPTED`).
