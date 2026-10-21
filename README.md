# Hubs Africa v2 — Plateforme d'Accompagnement & Suivi-Évaluation (CUBE S&E)

Plateforme web multi-tenant d'accompagnement de startups et PME en Afrique subsaharienne, basée sur la méthodologie de Suivi & Évaluation (S&E) du référentiel **CUBE**.

---

## 🚀 Stack Technique

- **Framework Backend** : Laravel 13 (PHP 8.4)
- **Interface & Panneaux d'Administration** : Filament v5 (Panels, Schemas, Tables, Forms, Infolists, Notifications, Actions)
- **Base de Données** : MySQL 8.x
- **Gestion des Rôles & Permissions** : Spatie Laravel-Permission & Filament Shield
- **Formulaires Dynamiques** : Packstub Filament Form Builder (étendu avec CUBE S&E)
- **Suite de Tests** : Pest PHP (16 tests fonctionnels, 80 assertions, 100% de réussite)

---

## 🏛️ Architecture Multi-Tenant & Portails

L'application isole strictement les données de chaque structure d'accompagnement à l'entrepreneuriat (**SAE**) grâce au trait `BelongsToTenant` et au scope global `TenantScope`.

### 1. Panel Structure SAE (`/sae/{tenant}`)
- Pilotage des cohortes, programmes, mentors et entrepreneurs.
- Gestion des sessions de coaching, des livrables et des jalons de niveau CUBE.
- Suivi des financements mobilisés (Subventions, Prêts d'honneur, Investissements).
- **Centre d'Évaluation & Enquêtes** (`EvaluationFormResource`) : Création de formulaires dynamiques, assignation par cohorte, relances en 1 clic et suivi de complétion.

### 2. Panel Coach / Mentor (`/mentor/{tenant}`)
- Inscription autonome par lien d'invitation sécurisé (`/coach/invite/{token}`).
- Suivi du portefeuille de projets assignés.
- Planification et comptes-rendus des séances de coaching individuel et ateliers collectifs.
- Évaluation des soumissions de livrables avec notation et retours constructifs.

### 3. Panel Entrepreneur (`/portal/{tenant}`)
- Consultation de l'état d'avancement du projet et du jalon actuel.
- Dépôt des versions de livrables attendus.
- Accès aux documents de la Data Room et ressources partagées.
- **Espace Questionnaires & Enquêtes** (`/portal/{tenant}/questionnaires-page`) :
  - Badge dynamique dans le menu indiquant le nombre de formulaires en attente.
  - Bannière d'alerte prioritaire sur le tableau de bord avec échéance et consignes.
  - Interface interactive de réponse (`/portal/{tenant}/questionnaires/{slug}`).
  - Historique complet avec consultation des réponses transmises.

---

## 📋 Cycle des Formulaires d'Évaluation

```
SAE (Création Formulaire CUBE) ──► Déblocage par Cohorte avec Échéance
                                                    │
                                                    ▼
           Entrepreneur notifié (Cloche + Badge Menu + Widget Dashboard)
                                                    │
                                                    ▼
                       Remplissage en ligne (Livewire Form Builder)
                                                    │
                                                    ▼
       Mise à jour FormUnlock (SOUMIS) + Événement Timeline + Alerte SAE
```

---

## 🧪 Tests Automatisés

Pour exécuter la suite de tests complète :

```bash
php artisan test
```

Résultats :
- `MultiTenancyAndInvitationTest` : Isolation multi-tenant et inscription coach par token.
- `PagesAndViewsTest` : Rendu des pages et des formulaires avec tiroirs `slideOver()`.
- `EvaluationFormSystemTest` : Typologie `FormType`, déblocage par cohorte, détection des retards, badge de navigation et cycle de soumission.

---

## 🔑 Identifiants de Test (Données de Démonstration)

Pour réinitialiser et peupler la base avec les données de démonstration :

```bash
php artisan migrate:fresh --seed
```

| Rôle | URL | Email | Mot de passe | SAE / Tenant |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | `/admin` | `admin@hubsafrica.org` | `password` | Global |
| **Responsable SAE** | `/sae/teranga-africa` | `fatou.diop@teranga.sn` | `password` | Teranga Africa Hub |
| **Coach / Mentor** | `/mentor/teranga-africa` | `marie.sarr@coach.sn` | `password` | Teranga Africa Hub |
| **Entrepreneur** | `/portal/teranga-africa` | `ousmane.fall@ecobat.sn` | `password` | Teranga Africa Hub |

---

Consultez le fichier [`CHANGELOG.md`](./CHANGELOG.md) pour le détail exhaustif des versions et des fonctionnalités.
