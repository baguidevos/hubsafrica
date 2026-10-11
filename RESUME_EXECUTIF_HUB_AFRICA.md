# 📄 RÉSUMÉ EXÉCUTIF (EXECUTIVE SUMMARY)
## PROJET : HUB AFRICA (HUB AFRICA V2)
*La Plateforme SaaS Panafricaine de Pilotage de l'Accompagnement, de Suivi-Évaluation (S&E) et de Préparation à l'Investissement*

---

| **Information Clé** | **Détail du Dossier** |
| :--- | :--- |
| **Nom du Produit** | **HUB AFRICA** (Plateforme SaaS Hub Africa v2) |
| **Nature de la Solution** | SaaS B2B Multi-Tenant d'Accompagnement & Suivi-Évaluation (S&E) |
| **Cible Principale** | Structures d'Accompagnement à l'Entrepreneuriat (**SAE**) : Incubateurs, Accélérateurs, Pépinières, Pôles Universitaires, Agences Nationales PME, Programmes Bailleurs |
| **Cible Secondaire** | Entrepreneurs accompagnés (Startups / TPME) & Réseau de Coachs / Mentors |
| **Siège & Rayonnement** | Lomé (Togo) — Déploiement Espace UEMOA, CEMAC & Panafricain |
| **Stade de Développement** | Produit déployé et testé (Laravel 13 / Filament v5) / Phase de commercialisation et passage à l'échelle |
| **Besoin de Financement** | **75 000 000 FCFA** (environ **114 335 EUR** / **125 000 USD**) |
| **Instrument Ciblé** | Augmentation de capital (Equity) / BSA-AIR (SAFE africain) / Subvention d'amorçage |
| **Contacts Officiels** | 📧 `contact@hubafrica.org` • `equipedecube@gmail.com` • 📱 WhatsApp : `+228 70 76 76 94` |
| **Plateforme Déployée** | Portail officiel Hub Africa & Environnements SAE Multi-Tenants (`/sae/{tenant}`) |

---

## 1. 🎯 Synthèse Managériale : Pourquoi Hub Africa ?

À travers l'Afrique subsaharienne, plus de **750 structures d'accompagnement à l'entrepreneuriat (SAE)** (incubateurs, accélérateurs, technopoles, programmes gouvernementaux et fondations) soutiennent des dizaines de milliers de jeunes pousses. Pourtant, **plus de 85% d'entre elles opèrent encore de façon artisanale** : suivi des cohortes sur des tableurs Excel dispersés, échanges informels sur WhatsApp, absence de traçabilité des livrables et incapacité notoire à produire des rapports d'impact consolidés exigibles par les bailleurs de fonds internationaux (AFD, GIZ, BAD, Union Européenne, Banque Mondiale).

En conséquence :
- **82% des startups accompagnées échouent avant 3 ans** par manque de suivi méthodique continu et d'évaluation rigoureuse de leurs jalons.
- **Les SAE consacrent 60% de leur temps à des tâches administratives manuelles** au détriment du coaching qualitatif.
- **Les bailleurs de fonds peinent à mesurer le retour sur investissement social et économique** de leurs programmes d'appui.

**HUB AFRICA** est la **solution SaaS tout-en-un conçue sur mesure pour l'Afrique** qui digitalise, standardise et professionnalise l'intégralité du cycle de vie de l'accompagnement entrepreneurial. Grâce à une architecture multi-tenant native, chaque SAE dispose de son espace privatif pour piloter ses programmes, suivre les séances de mentorat, administrer les questionnaires dynamiques de Suivi & Évaluation (S&E), évaluer les livrables par jury et préparer les startups à la levée de fonds via une Data Room intégrée.

---

## 2. ⚡ Le Problème du Marché de l'Accompagnement en Afrique

L'écosystème entrepreneurial africain souffre de 4 déficits majeurs que Hub Africa résout directement :

1. **La gestion manuelle et fragmentée des cohortes :** Les dossiers de candidatures, les présences aux ateliers, les comptes-rendus de mentorat et les livrables sont éparpillés entre boîtes emails, Google Drive et fichiers Excel non synchronisés.
2. **L'angle mort du Suivi-Évaluation (S&E) :** Les SAE ne disposent pas d'outils automatisés pour mesurer l'évolution des compétences, la création d'emplois, la progression du chiffre d'affaires et la santé financière des porteurs de projets au fil des phases.
3. **Le manque de standardisation méthodologique :** Les critères d'évaluation des jurys et les jalons de maturité varient d'un programme à l'autre sans continuité pédagogique.
4. **L'inaccessibilité des logiciels occidentaux :** Les outils ERP ou de gestion de projets internationaux (Asana, Notion, Salesforce, HubSpot) sont trop chers, facturés en devises fortes, non pensés pour les réalités de connectivité intermittente et totalement déconnectés des processus d'incubation africains.

---

## 3. 💡 La Solution Hub Africa : Une Plateforme SaaS Multi-Tenant Complète

Développée avec les technologies les plus modernes et robustes (Laravel 13, Filament v5, PHP 8.4, architecture multi-tenant stricte), **Hub Africa** propose 3 portails interconnectés avec isolation totale des données par structure :

```
┌────────────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   ARCHITECTURE DE HUB AFRICA                                       │
├────────────────────────────────┬──────────────────────────────────┬────────────────────────────────┤
│  1. PANEL STRUCTURE SAE        │  2. PANEL COACH / MENTOR         │  3. PANEL ENTREPRENEUR         │
│     `/sae/{tenant}`            │     `/mentor/{tenant}`           │     `/portal/{tenant}`         │
├────────────────────────────────┼──────────────────────────────────┼────────────────────────────────┤
│ • Gestion des programmes &     │ • Onboarding sécurisé par        │ • Vue claire sur le parcours & │
│   cohortes de startups         │   lien d'invitation avec token   │   les jalons de maturité       │
│ • Assignation des coachs &     │ • Portefeuille de startups       │ • Dépôt versionné des livrables│
│   suivi des présences          │   assignées en temps réel        │   attendus par phase           │
│ • Centre de formulaires &      │ • Planification et CR des        │ • Réponse interactive aux      │
│   enquêtes S&E dynamiques      │   séances individuelles/groupes  │   questionnaires S&E assignés  │
│ • Grilles de jury & sélection  │ • Notation et feedbacks          │ • Espace Data Room documentaire│
│ • Reporting consolidé bailleur │   constructifs sur livrables     │   pour investisseurs           │
└────────────────────────────────┴──────────────────────────────────┴────────────────────────────────┘
```

### Fonctionnalités Clés Uniques de Hub Africa :
- **Multi-Tenancy Natif (`BelongsToTenant`) :** Chaque incubateur dispose de son identité, son sous-domaine/slug, ses programmes et ses utilisateurs en étanchéité de données totale.
- **Moteur de Suivi & Évaluation (CUBE S&E) :** Générateur de formulaires dynamiques, déblocage conditionnel par cohorte, alertes automatiques et relances en 1 clic.
- **Portefeuille de Livrables & Versioning :** Traçabilité documentaire complète des business plans, études de marché et états financiers déposés par les entrepreneurs.
- **Data Room Intégrée :** Préparation native des startups à l'audit et à l'entrée d'investisseurs.

---

## 4. 📊 Le Modèle Économique (Business Model) de Hub Africa

Hub Africa déploie un **modèle SaaS B2B par abonnement récurrent**, complété par des licences de cohortes pour les bailleurs et des services à haute valeur ajoutée :

```
+----------------------------------------------------------------------------------------------------+
|                             FLUX DE REVENUS DE LA PLATEFORME HUB AFRICA                            |
+----------------------------------------------------------------------------------------------------+
|  1. ABONNEMENTS SAAS SAE (55%)   |  2. LICENCES PROGRAMMES BAILLEURS (30%) |  3. SERVICES ADD-ON (15%)     |
|  • Starter : 25 000 FCFA/mois    |  • Forfait 1,5M à 5M FCFA par cohorte   |  • Marque blanche complète   |
|  • Pro : 60 000 FCFA/mois        |    financée par les bailleurs (AFD,     |  • Formations des coachs     |
|  • Enterprise : 120 000 FCFA/mois|    GIZ, Enabel, BAD, UE)               |  • Modules d'audit S&E       |
+----------------------------------------------------------------------------------------------------+
```

- **Offre SaaS SAE Pro (Cœur de cible) :** **60 000 FCFA / mois** (~91 €/mois) par structure, donnant accès à des cohortes illimitées, jusqu'à 100 entrepreneurs suivis et 10 comptes mentors.
- **Licence Institutionnelle / Programme Bailleurs :** Facturation forfaitaire intégrée directement dans les lignes budgétaires d'assistance technique des grands programmes de développement (entre 1 500 000 et 5 000 000 FCFA par programme annuel de cohorte).
- **Rétention élevée (Stickiness) :** Une fois les historiques de cohortes et les données de suivi enregistrés dans Hub Africa, le coût de bascule vers un autre outil est dissuasif, garantissant un taux de rétention > 92%.

---

## 5. 🚀 Traction Réalisée & Preuves de Concept

Hub Africa repose sur des acquis concrets et un produit d'ores et déjà opérationnel :
- **Plateforme v2 complètement développée et testée :** 100% de réussite sur la suite de tests automatisés Pest (gestion des rôles, multi-tenancy, formulaires S&E, formulaires de notation jury).
- **100+ Structures d'Accompagnement (SAE) répertoriées** et qualifiées dans la base de données régionale.
- **1 200+ entrepreneurs accompagnés et tracés** à travers les programmes historiques et méthodologiques.
- **Déploiement pilote validé dans 5 pays :** Togo, Bénin, Côte d'Ivoire, Sénégal, Burkina Faso.

---

## 6. 🌐 Marché Cible & Potentiel d'Expansion

- **TAM (Marché Total Adressable) :** **75 Millions USD** — L'ensemble des dépenses logicielles et de digitalisation de l'accompagnement entrepreneurial en Afrique subsaharienne (> 1 500 incubateurs, 800 universités et des centaines de programmes d'agences d'État).
- **SAM (Marché Disponible) :** **18 Millions USD** — Les structures d'accompagnement formelles et programmes d'appui aux PME dans les 17 pays de l'espace francophone et OHADA.
- **SOM à 3 ans (Marché Visé par Hub Africa) :** **2,5 Millions USD (soit ~1,6 Milliard FCFA)** — Objectif de 180 SAE abonnées et 40 programmes institutionnels majeurs d'ici fin 2028.

---

## 7. 📈 Projections Financières Synthétiques de Hub Africa (2025 - 2028)

*Montants en Francs CFA et équivalents Euros*

| Indicateur de Performance | 2025 (N-1 Réalisé) | 2026 (N Budget) | 2027 (N+1 Projeté) | 2028 (N+2 Projeté) |
| :--- | :---: | :---: | :---: | :---: |
| **SAE abonnées à Hub Africa** | 15 | 40 | 95 | 180 |
| **Startups actives sur la plateforme** | 180 | 500 | 1 400 | 3 200 |
| **Chiffre d'Affaires Global Hub Africa** | **18 500 000 FCFA** | **58 000 000 FCFA** | **145 000 000 FCFA** | **320 000 000 FCFA** |
| *Équivalent Chiffre d'Affaires (€)* | *28 200 €* | *88 400 €* | *221 000 €* | *487 800 €* |
| **Charges d'Exploitation (OPEX)** | 24 200 000 FCFA | 49 500 000 FCFA | 98 000 000 FCFA | 185 000 000 FCFA |
| **Résultat d'Exploitation (EBITDA)** | **-5 700 000 FCFA** | **+8 500 000 FCFA** | **+47 000 000 FCFA** | **+135 000 000 FCFA** |
| **Marge Nette (%)** | *-30,8%* | **+14,6%** | **+32,4%** | **+42,1%** |

*Grâce à la nature 100% logicielle et scalable de Hub Africa, chaque nouvelle SAE abonnée génère une marge incrémentale supérieure à 85%.*

---

## 8. 💰 L'Offre Financière : Levée de Fonds de 75 000 000 FCFA pour Hub Africa

Pour accélérer la commercialisation de la plateforme et verrouiller la place de leader du SaaS d'incubation en Afrique francophone, **Hub Africa ouvre son tour d'amorçage de 75 000 000 FCFA** (~114 335 €).

### Allocation des Ressources :
- **40% (30,0 M FCFA) — Force Commerciale & Déploiement Terrain :** Recrutement de 4 Business Developers pays pour signer et intégrer les SAE au Bénin, en Côte d'Ivoire, au Sénégal et au Cameroun.
- **30% (22,5 M FCFA) — R&D Logicielle, UX Mobile & Sécurité Cloud :** Poursuite des développements (application mobile offline, connecteurs API, hébergement haute disponibilité).
- **20% (15,0 M FCFA) — Marketing B2B, Événements & Communauté SAE :** Webémissions régionales, présence aux sommets tech africains et organisation du Hub Africa Summit.
- **10% (7,5 M FCFA) — Trésorerie Tampon & Juridique :** Réserve opérationnelle de sécurité sur 12 mois.

### Conditions Proposées aux Investisseurs :
- **Valorisation pré-money :** **300 000 000 FCFA** (~457 000 €).
- **Participation cédée :** **20% du capital social de Hub Africa** (ou émission d'un BSA-AIR avec décote de 20% et cap à 350M FCFA).
- **Ticket d'entrée minimum :** 5 000 000 FCFA (~7 620 €).
- **Sortie projetée à 4-5 ans :** Rachat stratégique ou refinancement Série A avec un TRI cible de **45% à 65%/an**.

---

## 9. 🌍 Impact Socio-Économique (ODD)

- **ODD 8 (Travail décent & croissance) :** Plus de **10 000 emplois formalisés et suivis** sur la plateforme d'ici 3 ans.
- **ODD 5 (Égalité des sexes) :** Paramétrage d'indicateurs de genre et suivi de 45% minimum de femmes entrepreneures.
- **ODD 9 (Innovation et infrastructure) :** Dotation de l'Afrique de sa propre infrastructure technologique de souveraineté pour l'accompagnement des entreprises.

---

## 10. 📞 Contacts Officiels Hub Africa

- **Email Projet :** `contact@hubafrica.org` • `equipedecube@gmail.com`
- **WhatsApp Direct Promoteur :** `+228 70 76 76 94`
- **Portail Web :** [https://www.hubafrica.org](https://www.hubafrica.org) (et plateformes partenaires [saeicube.com](https://www.saeicube.com))
- **Siège :** Lomé, République Togolaise
