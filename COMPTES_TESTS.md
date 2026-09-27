# 🧪 Comptes de Test & Connexions Directes — Hub Africa

Ce document référence l'intégralité des **comptes de test pré-configurés** pour chaque rôle et espace de l'application Hub Africa.

---

## ⚡ Connexion Rapide en 1 Clic (Auto-Login)

Pour vous connecter **instantanément** à n'importe quel espace sans avoir à saisir votre mot de passe, cliquez directement sur le lien correspondant ci-dessous lorsque votre serveur local est lancé (`http://localhost:8000`) :

| Rôle & Profil | Nom | Connexion Directe 1-Clic | Dashboard Cible |
| :--- | :--- | :--- | :--- |
| 🛡️ **Super Administrateur** | Directeur Hub Africa | [👉 Se connecter en Super Admin](http://localhost:8000/demo/login?as=admin) | `/admin` |
| 🏢 **Responsable SAE (Dakar)** | Fatou Diop | [👉 Se connecter (Teranga Hub)](http://localhost:8000/demo/login?as=sae) | `/sae/teranga-africa` |
| 🏢 **Responsable SAE (Abidjan)** | Amadou Koné | [👉 Se connecter (Jokkolabs)](http://localhost:8000/demo/login?as=sae2) | `/sae/jokkolabs-abidjan` |
| 🎓 **Coach & Mentor Senior** | Marie Sarr | [👉 Se connecter (Marie Sarr)](http://localhost:8000/demo/login?as=coach) | `/coach/teranga-africa` |
| 🎓 **Coach Marketing & Pitch** | Ibrahima Diallo | [👉 Se connecter (I. Diallo)](http://localhost:8000/demo/login?as=coach2) | `/coach/teranga-africa` |
| 🚀 **Entrepreneur (EcoBat)** | Ousmane Fall | [👉 Se connecter (Ousmane Fall)](http://localhost:8000/demo/login?as=entrepreneur) | `/portal/teranga-africa` |
| 🚀 **Entrepreneure (SunuAgri)** | Awa Ndiaye | [👉 Se connecter (Awa Ndiaye)](http://localhost:8000/demo/login?as=entrepreneur2) | `/portal/teranga-africa` |

> 🚪 **Pour vous déconnecter rapidement** et changer de profil : [👉 Déconnexion](http://localhost:8000/demo/logout)

---

## 📋 Tableau Récapitulatif des Identifiants

Tous les comptes partagent le même mot de passe universel : `password`

| Rôle | Nom | Email | Mot de passe | Structure / Projet | Panel Filament |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Directeur Hub Africa | `admin@hubafrica.org` | `password` | *Plateforme Panafricaine* | [`/admin`](http://localhost:8000/admin) |
| **Responsable SAE** | Fatou Diop | `fatou.diop@teranga.sn` | `password` | Teranga Africa Hub (Dakar) | [`/sae/teranga-africa`](http://localhost:8000/sae/teranga-africa) |
| **Responsable SAE** | Amadou Koné | `amadou.kone@jokkolabs.ci` | `password` | Jokkolabs Abidjan (RCI) | [`/sae/jokkolabs-abidjan`](http://localhost:8000/sae/jokkolabs-abidjan) |
| **Coach / Mentor** | Marie Sarr | `marie.sarr@coach.sn` | `password` | Teranga Africa Hub | [`/coach/teranga-africa`](http://localhost:8000/coach/teranga-africa) |
| **Coach / Mentor** | Ibrahima Diallo | `ibrahima.diallo@coach.sn` | `password` | Teranga Africa Hub | [`/coach/teranga-africa`](http://localhost:8000/coach/teranga-africa) |
| **Entrepreneur** | Ousmane Fall | `ousmane.fall@ecobat.sn` | `password` | EcoBat Sénégal (BTP vert) | [`/portal/teranga-africa`](http://localhost:8000/portal/teranga-africa) |
| **Entrepreneure** | Awa Ndiaye | `awa.ndiaye@agritech.sn` | `password` | SunuAgri Tech (AgriTech) | [`/portal/teranga-africa`](http://localhost:8000/portal/teranga-africa) |

---

## 🎨 Boutons de Pré-remplissage sur chaque Page de Connexion

Sur **toutes les pages de connexion Filament** (`/admin/login`, `/sae/login`, `/coach/login`, `/portal/login`), un bandeau **« 🧪 Comptes de Test »** est directement intégré sous le formulaire avec deux boutons pour chaque compte :

1. **Bouton « Remplir »** : injecte instantanément l'email et le mot de passe dans les champs du formulaire.
2. **Bouton « 1-Clic »** : authentifie et redirige immédiatement vers le tableau de bord sans soumission manuelle.

---

## 🌐 Page Dédiée dans l'Application

Vous pouvez également accéder à la page interactive des comptes de test :
- URL directe : **[`http://localhost:8000/comptes-test`](http://localhost:8000/comptes-test)** (ou [`/demo`](http://localhost:8000/demo))
- Depuis le site public : Cliquez sur le badge **« 🧪 Comptes de Test (1-Clic) »** dans la barre supérieure de navigation ou dans le menu « Espaces Dédiés ».

---

## ⚙️ Réinitialisation des Données

Pour réinitialiser la base de données et recharger tous ces comptes avec les programmes et cohortes de démo :

```bash
php artisan migrate:fresh --seed
```
