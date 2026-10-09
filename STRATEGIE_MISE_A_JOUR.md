# Stratégie de Mise à Jour & Déploiement — Hub Africa V2
**Branche :** `feature/saei-accompaniment-tracking`  
**Date :** Octobre 2026  

---

## 1. Stratégie UX : Mise à jour du Profil SAE (`EditSaeProfile`)

### Constat actuel
* Lors de l'inscription (`RegisterSae.php`), l'onboarding utilise un **Wizard en 4 étapes** fluide et pédagogique.
* Sur la page de modification du profil (`EditSaeProfile.php`), les champs sont empilés de manière linéaire sans regroupement visuel, ce qui rend la navigation fastidieuse avec les éditeurs riches.

### Solution recommandée : Navigation par Onglets (Tabs)
Structurer le formulaire d'édition en **4 onglets thématiques natifs Filament** :

1. **🏢 Identité & Coordonnées**
   * Nom de la structure, slug URL.
   * Logo officiel avec recadrage circulaire (`circleCropper()`).
   * Contacts officiels : Email, Téléphone, Site web institutionnel, Adresse physique.
   * Présentation synthétique.

2. **🎯 Stratégie & Offre d'accompagnement**
   * Vision stratégique à long terme (`RichEditor`).
   * Mission & Raison d'être (`RichEditor`).
   * Objectifs prioritaires & Piliers d'impact (`RichEditor`).
   * Services & Offre d'incubation / accélération (`RichEditor`).

3. **⚖️ Gouvernance & Seuils de Sélection**
   * Seuil d'admission directe par défaut (sur 100).
   * Seuil de liste d'attente par défaut (sur 100).
   * Note explicative sur l'impact de ces seuils sur le scoring automatique des candidatures.

4. **📦 Méthodologies & Packs Actifs**
   * Récapitulatif visuel des modèles actifs rattachés à la SAE (Startup Loop Afric'innov, SAE-Acte).
   * Bouton de redirection vers la bibliothèque des modèles (`AccompanimentTemplatesPage.php`).

---

## 2. Stratégie Données : Rétrocompatibilité & Mise à Niveau des SAE Existantes

### Problématique
Le service `SaeOnboardingService.php` initialise automatiquement les packs de suivi, les formulaires standardisés et les indicateurs pour **toute nouvelle SAE créée**.  
Cependant, les structures créées avant cette release ne disposent pas encore de ces données.

### Plan d'action

#### A. Commande Artisan de rattrapage (Batch CLI - Idempotent)
Créer la commande `php artisan sae:upgrade-templates` :
* Détecte les SAE existantes.
* Vérifie si les formulaires d'évaluation et templates par défaut sont présents.
* Provisionne les modèles manquants **sans écraser** les personnalisations déjà effectuées.
* Consigne les actions dans un rapport d'exécution console.

#### B. Synchronisation à la demande (Dashboard SAE)
* Ajouter un bandeau d'information ou un bouton d'action dans le tableau de bord SAE :
  > *« De nouveaux modèles d'accompagnement et outils de suivi sont disponibles. [Synchroniser mes modèles] »*
* Permet aux responsables SAE d'activer les nouveautés à leur rythme.

---

## 3. Stratégie de Release & Déploiement Technique

### A. Phase de Pré-Déploiement (Tests & Intégrité)
Exécuter la suite complète de tests de non-régression :
```powershell
php artisan test tests/Feature/SaeRegistrationAndFormTest.php tests/Feature/AccompanimentTrackingPilotTest.php tests/Feature/PagesAndViewsTest.php
```

Vérifier le respect des standards de code :
```powershell
vendor/bin/pint --dirty --format agent
```

### B. Intégration Git (Merge vers Master)
```bash
git checkout master
git pull origin master
git merge feature/saei-accompaniment-tracking
git tag -a v2.4.0 -m "Release: SAE Onboarding Wizard, M&E Tracking Dashboard, Methodology Templates & Theme Harmonization"
git push origin master --tags
```

### C. Procédure de Déploiement en Production
1. **Mise à jour du code source :**
   ```bash
   git pull origin master
   ```

2. **Installation des dépendances :**
   ```bash
   composer install --no-dev --optimize-autoloader
   npm run build
   ```

3. **Exécution des migrations de base de données :**
   ```bash
   php artisan migrate --force
   ```
   *(Applique notamment `2026_10_07_192729_add_strategy_fields_to_saes_table.php`)*

4. **Lien de stockage pour les logos :**
   ```bash
   php artisan storage:link
   ```

5. **Exécution du rattrapage des SAE existantes :**
   ```bash
   php artisan sae:upgrade-templates
   ```

6. **Optimisation des caches Laravel & Filament :**
   ```bash
   php artisan optimize:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan filament:cache-components
   ```

---

## 4. Matrice de Suivi & Responsabilités

| Axe | Action | Statut | Priorité |
| :--- | :--- | :--- | :--- |
| **UX Profile** | Structurer `EditSaeProfile.php` en Tabs | À planifier | Haute |
| **Données** | Commande `sae:upgrade-templates` | À développer | Haute |
| **Données** | Action on-demand dans Dashboard SAE | Optionnel | Moyenne |
| **Release** | Tests automatisés + Pint | Validé | Critique |
| **Prod** | Migration base de données + Caches | Au déploiement | Critique |
