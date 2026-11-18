# Synthèse complète — KPIs SAE, Statuts & Parcours Candidats

Ce document résume l’ensemble des points abordés, les différences clés entre chaque mécanisme, et les correctifs déployés sur le panel SAE et le portail entrepreneur.

---

## 1. Tableau de bord SAE : ce qui a été corrigé et reconstruit

### A. Anomalies corrigées (données fictives & bugs)
- **Suppression de tous les fallbacks fictifs** :
  - Plus de `80 %` en dur sur le **Taux de validation** quand aucune évaluation n’existait.
  - Plus de capacité fictive `20` sur le **Taux de remplissage**.
  - Plus de faux secteurs (`Agriculture`, `Fintech`, `Énergie`, `Santé`) dans le graphique sectoriel.
  - Plus de fausse candidature (`1`) injectée sur les 6 derniers mois quand la base était vide.
- **Correction de l’inversion de couleur sur « Charge par coach »** : auparavant, un coach avec 1–2 projets apparaissait en rouge et un coach surchargé (6+ projets) en vert. Désormais : `emerald` ≤ 5 projets/coach, `amber` ≤ 8, `rose` > 8 ou 0 coach actif.
- **Alignement des Enums & colonnes réelles** :
  - Prise en compte des variantes `ProgramStatus::LANCE` + `ACTIF` et `CoachStatus::ACTIF` + `ACTIVE`.
  - Utilisation de `coaches.max_capacity` (au lieu de `capacity` inexistant) et `coaches.specialties` (JSON) dans [CoachWorkloadWidget.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Widgets/CoachWorkloadWidget.php).

### B. Structure actuelle des KPIs ([SaeStatsOverviewWidget.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Widgets/SaeStatsOverviewWidget.php) & [PerformanceRatesWidget.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Widgets/PerformanceRatesWidget.php))

| Axe métier | Cartes principales (`SaeStatsOverviewWidget`) | Ratios associés (`PerformanceRatesWidget`) |
| :--- | :--- | :--- |
| **Portefeuille & Inclusion** | **Entrepreneurs accompagnés** (`ACTIVE` avec projet ou enrôlement) · sous-texte : diplômés + `% femmes` (`gender = 'F'`)<br>**Projets en incubation** (`ACTIF`) · sous-texte : emplois (`team_size`) + projets `CANDIDAT`<br>**Programmes actifs** (`ACTIF`/`LANCE`) · cohortes `EN_COURS` & `OUVERTE` | **Remplissage cohortes** : enrôlés actifs / capacité des cohortes `OUVERTE` & `EN_COURS` |
| **Pipeline Candidatures** | **Appels à candidatures** (`OUVERT_CANDIDATURES`) · cliquable vers les candidatures filtrées<br>**Candidatures à traiter** (`SOUMISE` + `EN_EXAMEN`) · total reçues & admises | **Taux d’admission** : `ACCEPTEE` / décisions rendues (hors `BROUILLON` & `RETIREE`) |
| **Coaching & Livrables** | **Coachs mobilisés** · heures réalisées (`hours_delivered` / `duration`)<br>**Projets sans coach** (`ACTIF` sans `CoachAssignment` `ACTIF`)<br>**Livrables à évaluer** · dépôts en retard (`is_late`)<br>**Projets bloqués** · `STAGNANT` & `ABANDONNE` | **Charge par coach** : accompagnements actifs / coachs actifs<br>**Réalisation séances** : `REALISEE` / hors `ANNULEE`<br>**Taux de dépôt** & **Taux de validation** |
| **Impact & Financements** | **Financements décaissés** (`DECAISSE` + `INVESTI`) · sous-texte : montants `ACCORDE` en cours | **Couverture financement** : `amount_obtained` / `amount_sought` |

---

## 2. Différence entre « Appels à candidatures » et « Candidatures à traiter »

| Critère | Carte « Appels à candidatures » | Carte « Candidatures à traiter » |
| :--- | :--- | :--- |
| **Chiffre principal** | Nombre de programmes au statut `OUVERT_CANDIDATURES` (ex. `5`) | Nombre de candidatures en attente de décision (`SOUMISE` + `EN_EXAMEN`, ex. `140`) |
| **Portée du sous-texte** | Candidatures reçues (hors `BROUILLON`) **uniquement sur ces programmes ouverts** (ex. `120`) | **Toutes** les candidatures reçues sur **l’ensemble des programmes** de la SAE (y compris fermés, en sélection, lancés ou clôturés, ex. `140`) |
| **Action au clic** | Ouvre la liste des candidatures pré-filtrée sur `open_call_programs` ([ApplicationsTable.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/Applications/Tables/ApplicationsTable.php#L84-L90)) | — |

> [!NOTE]
> L’écart entre `120` et `140` correspond donc aux **20 candidatures** rattachées à d’autres programmes dont l’appel n’est plus au statut `OUVERT_CANDIDATURES`.

---

## 3. Cycle de vie des statuts : `CANDIDAT` vs `ACTIVE` / `ACTIF`

### Quand un `Entrepreneur` ou un `Project` est-il `CANDIDAT` ?
- Dès la soumission d’une candidature ([ProgramApplicationWizard.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Livewire/Public/ProgramApplicationWizard.php), [HandleCandidatureSubmission.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Listeners/HandleCandidatureSubmission.php)) ou l’inscription via une passerelle de cohorte ([CohortPublicController.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Http/Controllers/Public/CohortPublicController.php)).
- Ils sont **exclus** des compteurs d’incubation (*Entrepreneurs accompagnés*, *Projets en incubation*, *Projets sans coach*) et du Kanban *Paliers de Maturité*.

### Les candidats ont-ils toujours accès au portail (`/portal`) ?
**Oui.** L’accès au portail entrepreneur ([User.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Models/User.php#L152-L192)) dépend uniquement de :
1. L’existence du profil `Entrepreneur` (`canAccessPanel('entrepreneur')`),
2. La ligne dans la table pivot `sae_user` avec `role = 'ENTREPRENEUR'` et `status = 'ACTIVE'` (`getTenants('entrepreneur')`).

> [!TIP]
> Le `'status' => 'ACTIVE'` sur `sae_user` représente uniquement l’**activation de l’accès utilisateur** à la SAE (et non son statut d’accompagnement). Il n’impacte aucun KPI du tableau de bord.

### Quand passent-ils à `ACTIVE` (Entrepreneur) et `ACTIF` (Projet) ?
1. **À l’acceptation de la candidature par le jury (`ACCEPTEE`)** : `enrollInCohort()` dans [Application.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Models/Application.php#L105-L154) crée l’`Enrollment` (`ACTIF`), promeut l’entrepreneur `CANDIDAT` → `ACTIVE` et le projet → `ACTIF` sur le palier du programme.
2. **Par invitation directe** (`/join/{token}` ou `/entrepreneur/invite/{token}`) : création directe en `ACTIVE` / `ACTIF` + `Enrollment` immédiat dans la cohorte liée.
3. **Par création/modification manuelle** dans le back-office SAE (ou import V1).

---

## 4. Différence entre les 3 parcours d’entrée

| Parcours | Point d’entrée | Statut créé | `Enrollment` créé ? | `Application` créée ? |
| :--- | :--- | :--- | :--- | :--- |
| **1. Formulaire / Wizard de candidature** | `/programmes/{id}/candidater` ou FormBuilder | `CANDIDAT` / `CANDIDAT` | Non (créé uniquement si `ACCEPTEE`) | Oui (`SOUMISE`) |
| **2. Passerelle publique de cohorte** | `/join/cohort/{id}` ou `/cohortes/{id}/rejoindre` | `CANDIDAT` | Non (redirige vers le formulaire de candidature) | Oui si le candidat finalise l’étape 2 |
| **3. Invitation privée directe** | `/join/{token}` ou `/entrepreneur/invite/{token}` | `ACTIVE` / `ACTIF` | **Oui, obligatoire** (cohorte requise dans [EntrepreneurInvitationForm.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/EntrepreneurInvitations/Schemas/EntrepreneurInvitationForm.php#L53-L74)) | Non |

---

## 5. Résolution du cas des 148 entrepreneurs sans projet

### Origine du problème
Des visiteurs passés par `/join/cohort/{id}` ont créé leur compte à l’étape 1 (`CohortPublicController::register()` qui mettait alors `ACTIVE`) puis ont abandonné avant de soumettre le formulaire de candidature à l’étape 2. Résultat : **148 `Entrepreneur` en `ACTIVE`, avec 0 `Project` et 0 `Application`**.

### Solutions mises en place
1. **Statistiques protégées** : [SaeStatsOverviewWidget.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Widgets/SaeStatsOverviewWidget.php#L35-L38) n’inclut que les entrepreneurs ayant au moins un `Project` ou un `Enrollment`.
2. **Correction à la source** : [CohortPublicController.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Http/Controllers/Public/CohortPublicController.php#L123) crée désormais ces inscrits en `CANDIDAT`.
3. **Relance en masse depuis le back-office** ([EntrepreneursTable.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Filament/Sae/Resources/Entrepreneurs/Tables/EntrepreneursTable.php#L112-L298)) :
   - Filtre `Fiche projet` → `Sans projet` pour les isoler en 1 clic.
   - BulkAction **« Inviter à compléter le projet & rattacher à une cohorte »** : crée l’`Enrollment`, garantit l’accès portail et envoie l’e-mail [CompleteProjectCohortInviteMail.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Mail/CompleteProjectCohortInviteMail.php).
4. **Blocage sur le portail tant que le projet n’est pas créé** ([EnsureEntrepreneurHasProject.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Http/Middleware/EnsureEntrepreneurHasProject.php)) :
   - Tout entrepreneur sans projet est restreint au **tableau de bord** et à la **création de fiche projet** ; les autres menus redirigent vers le tableau de bord jusqu’à ce que le projet soit enregistré.

---

## 6. Script de rattrapage en production (`sae:fix-candidate-stats`)

Disponible en CLI et via `/maintenance/fix-candidate-stats` ([BackfillCandidateStatusesCommand.php](file:///c:/Users/D3vOs/Projets/Javascript(Next_Nuxt_React_VueJs)/Hub_Africa_V2/hubafrica_v2/app/Console/Commands/BackfillCandidateStatusesCommand.php)) :
- **Projets `ACTIF` → `CANDIDAT`** : uniquement ceux liés à une `Application`, sans `Application` `ACCEPTEE`, sans `Enrollment` et sans `CoachAssignment` `ACTIF` (les créations manuelles sans candidature restent intactes).
- **Entrepreneurs `ACTIVE` → `CANDIDAT`** : uniquement ceux liés à une `Application`, sans `Application` `ACCEPTEE`, sans `Enrollment` et sans autre projet actif.
- **Accès portail** : rattache le pivot `sae_user` (`ENTREPRENEUR`, `ACTIVE`) s’il manquait.
