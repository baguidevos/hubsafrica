<x-filament-panels::page>
    <div class="space-y-8 pb-12">
        {{-- En-tête / Hero Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-teal-700 via-teal-800 to-slate-900 p-8 text-white shadow-lg">
            <div class="relative z-10 max-w-4xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full bg-teal-500/20 px-3 py-1 text-xs font-semibold tracking-wide text-teal-200 backdrop-blur-md border border-teal-400/30">
                    <x-heroicon-s-sparkles class="h-4 w-4 text-teal-300" />
                    <span>Documentation Officielle &middot; Hub Africa V2</span>
                </div>
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-white">
                    Guide Méthodologique & Pratique de l'Accompagnateur SAE
                </h1>
                <p class="text-base text-teal-100/90 leading-relaxed">
                    Ce guide interactif détaille l'organisation par <strong>Clusters</strong> de la plateforme et documente pas à pas chaque action des Responsables SAE. Suivez le cas pratique d'un accompagnement de A à Z avec la startup <strong>BioAgri Tech</strong>, depuis l'appel à projets jusqu'à la mesure d'impact final.
                </p>
                <div class="pt-2 flex flex-wrap gap-3 text-xs font-medium">
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 backdrop-blur-sm border border-white/10">
                        <x-heroicon-o-squares-2x2 class="h-4 w-4 text-teal-300" /> 4 Clusters Métiers
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 backdrop-blur-sm border border-white/10">
                        <x-heroicon-o-academic-cap class="h-4 w-4 text-amber-300" /> Cas Pratique BioAgri Tech (8 étapes)
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-3 py-1.5 backdrop-blur-sm border border-white/10">
                        <x-heroicon-o-camera class="h-4 w-4 text-emerald-300" /> 8 Emplacements de Captures d'Écran
                    </span>
                </div>
            </div>
            <div class="absolute -right-12 -bottom-16 w-80 h-80 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
        </div>

        {{-- Barre de Sommaire / Navigation Rapide --}}
        <div class="sticky top-4 z-20 rounded-xl border border-gray-200 bg-white/95 p-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 overflow-x-auto pb-1 sm:pb-0">
                <span class="font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider shrink-0 flex items-center gap-1">
                    <x-heroicon-o-bars-3-bottom-left class="h-4 w-4 text-teal-600" /> Navigation :
                </span>
                <a href="#fil-rouge" class="rounded-lg px-2.5 py-1 text-teal-700 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-teal-950/50 shrink-0 font-semibold">
                    ⭐ Fil Rouge BioAgri Tech
                </a>
                <span class="text-gray-300 dark:text-gray-700">|</span>
                <a href="#cluster-programmes" class="rounded-lg px-2.5 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 shrink-0">
                    1. Programmes & Sélection
                </a>
                <a href="#cluster-portefeuille" class="rounded-lg px-2.5 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 shrink-0">
                    2. Portefeuille Startups
                </a>
                <a href="#cluster-accompagnement" class="rounded-lg px-2.5 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 shrink-0">
                    3. Accompagnement & Mentorat
                </a>
                <a href="#cluster-impact" class="rounded-lg px-2.5 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 shrink-0">
                    4. Impact, Finance & Évaluation
                </a>
                <a href="#module-pilotage" class="rounded-lg px-2.5 py-1 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 shrink-0">
                    5. Pilotage & Décision
                </a>
            </div>
        </div>

        {{-- SECTION 1 : VUE D'ENSEMBLE DES 4 CLUSTERS FILAMENT --}}
        <div class="space-y-4">
            <div class="border-b border-gray-200 pb-3 dark:border-gray-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-squares-2x2 class="h-6 w-6 text-teal-600" />
                    Nouvelle Organisation par Clusters dans le Menu
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Les ressources sont désormais regroupées par grands domaines métiers dans le panneau latéral gauche.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Cluster 1 --}}
                <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 dark:border-indigo-900/50 dark:bg-indigo-950/20">
                    <div class="flex items-center gap-2 font-bold text-indigo-950 dark:text-indigo-200 text-sm">
                        <div class="p-1.5 rounded-lg bg-indigo-600 text-white">
                            <x-heroicon-o-academic-cap class="h-4 w-4" />
                        </div>
                        Programmes & Sélection
                    </div>
                    <ul class="mt-3 space-y-1 text-xs text-indigo-900 dark:text-indigo-300">
                        <li>• <strong>Programmes :</strong> Parcours & onglet jalons</li>
                        <li>• <strong>Cohortes :</strong> Promotions actives</li>
                        <li>• <strong>Jalons & Phases :</strong> Vue & gestion directe</li>
                        <li>• <strong>Candidatures :</strong> Instruction & Jurys</li>
                        <li>• <strong>Partenaires :</strong> Bailleurs & sponsors</li>
                        <li>• <strong>Niveaux :</strong> Échelle de maturité</li>
                    </ul>
                </div>

                {{-- Cluster 2 --}}
                <div class="rounded-xl border border-sky-200 bg-sky-50/40 p-4 dark:border-sky-900/50 dark:bg-sky-950/20">
                    <div class="flex items-center gap-2 font-bold text-sky-950 dark:text-sky-200 text-sm">
                        <div class="p-1.5 rounded-lg bg-sky-600 text-white">
                            <x-heroicon-o-briefcase class="h-4 w-4" />
                        </div>
                        Portefeuille Startups
                    </div>
                    <ul class="mt-3 space-y-1 text-xs text-sky-900 dark:text-sky-300">
                        <li>• <strong>Projets :</strong> Fiches 360° & Paliers</li>
                        <li>• <strong>Paliers :</strong> Entrée, Sortie & Durée</li>
                        <li>• <strong>Entrepreneurs :</strong> Profils uniques porteurs</li>
                        <li>• <strong>Journal (Timeline) :</strong> Événements & jalons clés</li>
                    </ul>
                </div>

                {{-- Cluster 3 --}}
                <div class="rounded-xl border border-teal-200 bg-teal-50/40 p-4 dark:border-teal-900/50 dark:bg-teal-950/20">
                    <div class="flex items-center gap-2 font-bold text-teal-950 dark:text-teal-200 text-sm">
                        <div class="p-1.5 rounded-lg bg-teal-600 text-white">
                            <x-heroicon-o-user-group class="h-4 w-4" />
                        </div>
                        Accompagnement & Mentorat
                    </div>
                    <ul class="mt-3 space-y-1 text-xs text-teal-900 dark:text-teal-300">
                        <li>• <strong>Séances & Ateliers :</strong> Émargements</li>
                        <li>• <strong>Coachs & Mentors :</strong> Annuaire & charge</li>
                        <li>• <strong>Invitations :</strong> Liens sécurisés</li>
                        <li>• <strong>Livrables soumis :</strong> Dépôts & GED</li>
                        <li>• <strong>Livrables attendus :</strong> Référentiel</li>
                        <li>• <strong>Ressources :</strong> Bibliothèque</li>
                        <li>• <strong>Calendrier :</strong> Planning mensuel</li>
                    </ul>
                </div>

                {{-- Cluster 4 --}}
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4 dark:border-emerald-900/50 dark:bg-emerald-950/20">
                    <div class="flex items-center gap-2 font-bold text-emerald-950 dark:text-emerald-200 text-sm">
                        <div class="p-1.5 rounded-lg bg-emerald-600 text-white">
                            <x-heroicon-o-chart-bar-square class="h-4 w-4" />
                        </div>
                        Impact & Finance
                    </div>
                    <ul class="mt-3 space-y-1 text-xs text-emerald-900 dark:text-emerald-300">
                        <li>• <strong>Indicateurs S&E :</strong> KPI périodiques</li>
                        <li>• <strong>Financements :</strong> Subventions & levées</li>
                        <li>• <strong>Data Room :</strong> Coffre-fort documentaire</li>
                        <li>• <strong>Formulaires :</strong> Enquêtes & diagnostics</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- SECTION 2 : LE CAS PRATIQUE FIL ROUGE "BIOAGRI TECH" --}}
        <div id="fil-rouge" class="space-y-6 pt-4">
            <div class="rounded-2xl border border-amber-300 bg-gradient-to-r from-amber-50 to-orange-50 p-6 dark:border-amber-900/50 dark:from-amber-950/20 dark:to-orange-950/20">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white font-bold text-lg shadow-sm">
                        ⭐
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-amber-950 dark:text-amber-100">
                            Cas Pratique Fil Rouge : Accompagnement Complet de « BioAgri Tech »
                        </h2>
                        <p class="text-xs text-amber-800/90 dark:text-amber-300">
                            Scénario réel d'une startup incubée du premier jour jusqu'au bilan d'impact final.
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs bg-white/70 p-3 rounded-xl dark:bg-gray-900/70 border border-amber-200/50 dark:border-amber-900/30">
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block font-medium">Startup :</span>
                        <strong class="text-gray-900 dark:text-white">BioAgri Tech SARL</strong> (Solutions biofertilisants IoT)
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block font-medium">Fondateur :</span>
                        <strong class="text-gray-900 dark:text-white">Amadou Traoré</strong> (28 ans, Côte d'Ivoire)
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block font-medium">Coach Référent :</span>
                        <strong class="text-gray-900 dark:text-white">Dr. Koffi N'Guessan</strong> (Agro-Business)
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 block font-medium">Partenaire Bailleur :</span>
                        <strong class="text-gray-900 dark:text-white">Fonds Ouest-Africain AgriTech (FOA)</strong>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 1 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">1</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 1 : Conception du Programme « Accélération AgriTech 2026 »
                            </h3>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Cluster : Programmes & Sélection &gt; Programmes</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-semibold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                        Cadrage Pédagogique
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Le Responsable SAE commence par concevoir le programme en définissant sa structure chronologique, sa grille de sélection et ses bailleurs de fonds :
                    </p>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2 text-xs">
                        <li><strong>Création du Programme :</strong> Saisir le nom <em>« Accélération AgriTech 2026 »</em>, description et objectifs d'impact.</li>
                        <li><strong>Onglet « Phases & Jalons » :</strong> Ajouter les 4 phases séquentielles :
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Phase 1 : Diagnostic & Boot Camp (4 sem.)</span> &rarr;
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Phase 2 : Structuration MVP & Business Plan (8 sem.)</span> &rarr;
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Phase 3 : Validation Marché & Traction (8 sem.)</span> &rarr;
                            <span class="text-indigo-600 dark:text-indigo-400 font-medium">Phase 4 : Demo Day & Levée de fonds (4 sem.)</span>.
                        </li>
                        <li><strong>Onglet « Grille de notation & Critères Jury » :</strong> Paramétrer 4 critères pondérés :
                            <em>Innovation & Scalabilité</em> (coeff 3, note/20), <em>Qualité de l'équipe</em> (coeff 2, note/20), <em>Impact social/environnemental</em> (coeff 2, note/20), <em>Faisabilité financière</em> (coeff 1, note/20).
                        </li>
                        <li><strong>Onglet « Partenaires & Bailleurs associés » :</strong> Cliquer sur <em>« Associer un partenaire »</em>, sélectionner l'institution (FOA) dans la liste déroulante, préciser son rôle (<em>Bailleur Principal</em>) et sa contribution financière (<em>45 000 000 FCFA</em>).</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 1 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 p-5 dark:border-indigo-800/80 dark:bg-indigo-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°1 à insérer
                                </h4>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    Recommandation : 1440 &times; 800 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Vue d'édition du Programme avec les onglets combinés au sommet (« Phases », « Critères d'évaluation » et « Partenaires »).
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> Formulaire d'un programme ouvert montrant les onglets en haut de page, les 4 phases ordonnées, la liste des critères avec leurs coefficients et le bailleur rattaché.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 2 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">2</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 2 : Lancement de la Cohorte, Synchronisation des Jalons & Projection Calendrier
                            </h3>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Cluster : Programmes & Sélection &gt; Cohortes &amp; Jalons</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        Automatisation
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Pour démarrer la promotion opérationnelle, le responsable crée la cohorte puis synchronise automatiquement les dates :
                    </p>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2 text-xs">
                        <li>Créer la cohorte <strong>« AgriTech Promo 1 (2026) »</strong> rattachée au programme, avec date de début fixée au <strong>01/10/2026</strong>.</li>
                        <li><strong>Choix Obligatoire du Modèle de Candidature :</strong> Sélectionner le formulaire de candidature requis (le <em>« Dossier Standardisé Hub Africa »</em> à 13 champs communs sélectionné par défaut, ou un template sur-mesure créé par votre SAE). À l'enregistrement, la plateforme bascule immédiatement sur les onglets de la promotion.</li>
                        <li>Dans l'onglet <strong>« Jalons réels & Calendrier de la cohorte »</strong> (au sommet de la fiche) ou depuis le menu direct <strong>« Jalons & Phases »</strong>, cliquer sur le bouton <strong>« Synchroniser depuis le programme »</strong>.</li>
                        <li><strong>Action automatique de la plateforme :</strong> Toutes les phases du programme sont copiées. Les dates de début et fin de chaque jalon sont immédiatement calculées et <strong>projetées automatiquement sur le Calendrier Global</strong>.</li>
                        <li><strong>Génération du Lien Direct de Candidature :</strong> Depuis la table des cohortes ou en en-tête d'édition, cliquer sur l'action <strong>« Lien direct »</strong>. Une modale interactive permet de copier l'URL (`/cohortes/{id}/rejoindre`) pour la partager par WhatsApp ou email. Les nouveaux candidats créent leur compte en 30 secondes et accèdent au formulaire pré-verrouillé sur la promotion avec les questions spécifiques de la structure à l'Étape 3 ; les candidats existants se connectent directement.</li>
                        <li><strong>Extraction de la Matrice des Présences :</strong> L'action <strong>« Présences Excel »</strong> permet d'exporter le fichier récapitulatif complet de l'assiduité de toute la promotion sur l'ensemble des séances.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 2 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 p-5 dark:border-indigo-800/80 dark:bg-indigo-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°2 à insérer
                                </h4>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    Recommandation : 1440 &times; 700 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Fiche Cohorte avec le bouton d'action « Synchroniser depuis le programme » et le tableau des phases calculées.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> La notification de succès « X phases synchronisées avec succès » et la liste des dates calculées (ex : 01/10 au 28/10, 29/10 au 23/12...).
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 3 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">3</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 3 : Candidature de « BioAgri Tech », Vue Unifiée & Notation Pondérée du Jury
                            </h3>
                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">Cluster : Programmes & Sélection &gt; Candidatures | Portail Coach &gt; Candidatures & Jurys</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                        Sélection & Scoring
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        L'entrepreneur <strong>Amadou Traoré</strong> soumet son dossier pour le projet <em>BioAgri Tech</em>. Le responsable et les membres du jury accèdent à une <strong>vue d'instruction unifiée haute fidélité</strong> :
                    </p>
                    <ol class="list-decimal list-inside space-y-2 pl-2 text-xs">
                        <li><strong>Vue Détaillée Harmonisée (3 Espaces) :</strong> En ouvrant le dossier, la page affiche le bandeau de statut, le stepper d'avancement à 4 étapes (*Soumission*, *Éligibilité*, *Jury*, *Délibération*) et la note consolidée du jury sur 100 avec coloration dynamique.</li>
                        <li><strong>Actions Rapides de Délibération (En-tête) :</strong>
                            Le responsable et les jurés disposent de boutons d'action immédiats :
                            <em>« 🎓 Noter le dossier »</em> (modale critère par critère), <em>« 🏆 Accepter »</em> (enrôlement direct en cohorte), <em>« ⏳ Mettre en attente »</em>, <em>« ❌ Refuser »</em> (avec motif obligatoire) et <em>« 💡 Fiche Projet »</em>.
                        </li>
                        <li><strong>Espace Jurys pour les Coachs :</strong> Les coachs disposent d'un menu dédié <code>/coach/{tenant}/applications</code> pour évaluer les dossiers sans avoir accès aux fonctions d'administration de la structure.</li>
                        <li><strong>Notation Critères Pondérés :</strong> La modale d'évaluation charge automatiquement la grille du programme :
                            <ul class="list-disc list-inside pl-4 text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                <li>Innovation & Scalabilité (coeff 3) : 17 / 20</li>
                                <li>Qualité de l'équipe (coeff 2) : 18 / 20</li>
                                <li>Impact social & environnemental (coeff 2) : 19 / 20</li>
                                <li>Faisabilité financière (coeff 1) : 15 / 20</li>
                            </ul>
                        </li>
                        <li><strong>Calcul Automatique & Décision Intelligente :</strong> La plateforme calcule le score pondéré : <strong class="text-emerald-600 dark:text-emerald-400">86,5 / 100</strong>. Un badge vert vif s'affiche (≥ 75) et le statut de la candidature bascule automatiquement en <strong>« Acceptée »</strong> (ou « Liste d'attente » pour 50-74, « Refusée » < 50, avec dérogation manuelle possible).</li>
                        <li><strong>Propagation Automatique & Enrôlement :</strong> Le projet est instantanément enrôlé dans la promotion (<code>Enrollment</code> actif), son statut passe à <strong>« ACTIF »</strong>, son palier d'entrée est automatiquement figé (<code>initial_level_id</code>), son premier historique de niveau est créé et un jalon chronologique (<em>Milestone</em>) est inscrit dans sa timeline.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 3 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/50 p-5 dark:border-indigo-800/80 dark:bg-indigo-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°3 à insérer
                                </h4>
                                <span class="rounded bg-indigo-100 px-2 py-0.5 text-[11px] font-semibold text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200">
                                    Recommandation : 1440 &times; 850 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Vue unifiée de candidature avec bandeau de statut, note jury sur 100, boutons de délibération d'en-tête et modale d'évaluation.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> La page détaillée de candidature affichant les critères pondérés, les actions de décision rapide (Accepter, Refuser) et la note globale verte.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 4 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-600 text-white font-bold text-sm">4</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 4 : Intégration au Portefeuille, Traçabilité des Paliers & Attribution du Coach
                            </h3>
                            <span class="text-xs text-sky-600 dark:text-sky-400 font-semibold">Clusters : Portefeuille Startups &gt; Projets | Accompagnement &gt; Coachs | Espace Entrepreneur</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-semibold text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                        Traçabilité Paliers & Coach
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Le projet <strong>BioAgri Tech</strong> fait désormais partie du portefeuille actif de la SAE. L'équipe pilote son évolution avec une traçabilité intégrale :
                    </p>
                    <ol class="list-decimal list-inside space-y-2 pl-2 text-xs">
                        <li><strong>Vérification Charge & Mentorat :</strong> Dans <em>Accompagnement &gt; Coachs</em>, vérifier que <strong>Dr. Koffi N'Guessan</strong> suit 4 startups sur un plafond de 6. Ouvrir la fiche du projet dans <em>Portefeuille &gt; Projets</em> et lui assigner la startup comme <strong>Coach Référent</strong>.</li>
                        <li><strong>Traçabilité Automatique du Niveau d'Entrée :</strong> La plateforme verrouille automatiquement le <strong>Palier d'entrée</strong> (<code>initial_level_id</code> = <em>Phase 1 : Diagnostic & Boot Camp</em>) ainsi que la date d'entrée (<code>current_level_entered_at</code>).</li>
                        <li><strong>Onglet « Historique des Paliers » & Bouton Promotion :</strong> Dans l'onglet dédié de la fiche projet SAE, chaque jalon est consigné de façon immuable (palier initial, date d'entrée, durée calculée, validateur). Le bouton <strong>« Promouvoir / Changer de palier »</strong> permet de passer la startup au niveau supérieur (<em>Phase 2 : Structuration MVP</em>) avec saisie obligatoire d'un motif. La plateforme clôture l'ancien palier, ouvre le nouveau et insère un événement <em>MILESTONE</em> dans la Timeline.</li>
                        <li><strong>Traçabilité Automatique du Palier de Sortie :</strong> Dès que le projet termine son parcours (statut <em>Diplômé</em>, <em>Sorti</em> ou <em>Abandonné</em>), son <strong>Palier de sortie</strong> (<code>exit_level_id</code>) et sa date exacte de fin (<code>exited_at</code>) sont automatiquement archivés.</li>
                        <li><strong>Expérience Immersive Porteur (Vue 360°) :</strong>
                            Côté porteur de projet (<code>/entrepreneur</code>), la règle d'unicité stricte (1 entrepreneur = 1 projet actif) oriente immédiatement l'utilisateur vers sa <strong>fiche projet 360°</strong> arborant un <strong>bloc dynamique « Parcours d'accompagnement & Paliers »</strong> avec stepper visuel (<em>Entrée &rarr; Actuel &rarr; Sortie</em>) et timeline chronologique des passages validés.
                        </li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 4 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-sky-300 bg-sky-50/50 p-5 dark:border-sky-800/80 dark:bg-sky-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-sky-900 dark:text-sky-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°4 à insérer
                                </h4>
                                <span class="rounded bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-800 dark:bg-sky-900 dark:text-sky-200">
                                    Recommandation : 1440 &times; 850 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Fiche détaillée du projet BioAgri Tech avec l'onglet « Historique des Paliers » et l'action « Promouvoir / Changer de palier ».
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> Formulaire et onglet relationnel des paliers montrant les dates de début/fin, la durée calculée et le coach référent assigné.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 5 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white font-bold text-sm">5</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 5 : Séances de Coaching, Ateliers Collectifs, Émargement Excel &amp; Calendrier Global
                            </h3>
                            <span class="text-xs text-teal-600 dark:text-teal-400 font-semibold">Cluster : Accompagnement &amp; Mentorat &gt; Séances &amp; Calendrier global</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-semibold text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                        Mentorat Actif
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Le parcours d'accompagnement combine sessions individuelles, ateliers collectifs et émargement hybride Excel :
                    </p>
                    <ol class="list-decimal list-inside space-y-2 pl-2 text-xs">
                        <li><strong>Planification &amp; Peuplement Cohorte :</strong> Le responsable planifie une Masterclass <em>« Proposition de Valeur &amp; Segmentation B2B »</em> rattachée à la promotion. Un clic sur <strong>« Peupler depuis la cohorte »</strong> insère automatiquement tous les entrepreneurs inscrits.</li>
                        <li><strong>Cycle d'Émargement Excel Hybride :</strong>
                            <ul class="list-disc list-inside pl-4 mt-1 space-y-0.5">
                                <li><strong>Export Excel / CSV en 1 clic :</strong> Bouton <em>« Exporter la feuille (Excel / CSV) »</em> générant le fichier pré-rempli avec encodage UTF-8 et colonnes : `ID_Projet`, `Nom_Startup`, `Entrepreneur`, `Email`, `Telephone`, `Presence`, `Observations`, `Retour_Startup`.</li>
                                <li><strong>Pointage Hors-Ligne :</strong> Le coach ou animateur annote les présences (*OUI* / *NON*) et observations directement sous Microsoft Excel.</li>
                                <li><strong>Ré-importation &amp; Synchronisation directe :</strong> Bouton <em>« Importer feuille complétée »</em> pour téléverser le fichier rempli. Les statuts, commutateurs de présence et notes sont mis à jour instantanément sur la plateforme.</li>
                            </ul>
                        </li>
                        <li><strong>Feuille Signée archivée comme Livrable Certifié :</strong> Action <strong>« Joindre feuille signée / Livrable »</strong> pour déposer le scan PDF ou photo émargé. Le document est immédiatement conservé dans l'onglet <strong>« Feuilles signées &amp; Livrables (GED) »</strong> avec versionnage pour justifier l'activité auprès des bailleurs.</li>
                        <li><strong>Matrice Globale de Cohorte :</strong> Depuis l'onglet <em>« Séances &amp; Émargements »</em> de la promotion, le bouton <strong>« Matrice globale (Excel) »</strong> génère le tableau récapitulatif complet de toutes les séances et calcule le taux d'assiduité de chaque startup.</li>
                        <li><strong>Projection sur le Calendrier Global :</strong> Séances individuelles, ateliers, lancements et jalons de promotions s'affichent avec pastilles de couleur, filtres interactifs et fiches modales au clic.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 5 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/50 p-5 dark:border-teal-800/80 dark:bg-teal-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°5 à insérer
                                </h4>
                                <span class="rounded bg-teal-100 px-2 py-0.5 text-[11px] font-semibold text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                    Recommandation : 1440 &times; 800 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Feuille d'émargement de séance avec actions Excel (export/import), onglet GED des feuilles signées et vue du calendrier.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> La fiche de séance avec les boutons *Exporter feuille (Excel)*, *Importer feuille complétée* et *Joindre feuille signée*, ainsi que le tableau des participants avec commutateurs de présence.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 6 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-600 text-white font-bold text-sm">6</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 6 : Dépôt du Livrable, Révision & GED Polymorphe Versionnée
                            </h3>
                            <span class="text-xs text-teal-600 dark:text-teal-400 font-semibold">Cluster : Accompagnement & Mentorat &gt; Livrables soumis</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                        GED Versionnée
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Le passage de la Phase 1 à la Phase 2 exige la remise du livrable <em>« Business Model Canvas & Plan de Trésorerie »</em> :
                    </p>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2 text-xs">
                        <li>Amadou dépose la <strong>version 1</strong> (PDF). Dr. Koffi l'analyse, demande des compléments sur la structure de coûts et attribue le statut <em>« Révisions requises »</em> avec ses annotations.</li>
                        <li>Amadou corrige son document et dépose la <strong>version 2</strong>.</li>
                        <li><strong>Archivage intelligent :</strong> La plateforme archive automatiquement l'ancienne version dans l'onglet relationnel <strong>« Versions du livrable (GED) »</strong> (`v1`, `v2`). L'historique complet reste téléchargeable en 1 clic.</li>
                        <li>Dr. Koffi valide la version 2 avec la note <strong>18 / 20</strong> et le statut <em>« Validé »</em>. Le jalon est coché.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 6 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-teal-300 bg-teal-50/50 p-5 dark:border-teal-800/80 dark:bg-teal-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°6 à insérer
                                </h4>
                                <span class="rounded bg-teal-100 px-2 py-0.5 text-[11px] font-semibold text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                    Recommandation : 1440 &times; 750 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Fiche du livrable validé avec l'onglet « Versions du livrable (GED) » montrant les versions v1 et v2.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> Table des versions avec dates de dépôt, badges de versionnement et bouton d'action de téléchargement immédiat.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 7 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white font-bold text-sm">7</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 7 : Mesure de l'Impact (KPI), Financement Décaissé & Coffre Data Room
                            </h3>
                            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold">Cluster : Impact, Finance & Évaluation &gt; Indicateurs, Financements & Data Room</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                        Impact & Métriques
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Au cours de la Phase 3 (Validation Marché), la startup amorce ses premières ventes et sécurise une subvention :
                    </p>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2 text-xs">
                        <li><strong>Suivi des Indicateurs (KPI) :</strong> Le responsable ou le coach saisit les relevés périodiques dans <em>Impact &gt; Indicateurs S&E</em> :
                            <ul class="list-disc list-inside pl-4 text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                <li>Indicateur <em>« Chiffre d'Affaires Mensuel (FCFA) »</em> : Octobre = 1 500 000 FCFA | Novembre = 3 200 000 FCFA.</li>
                                <li>Indicateur <em>« Emplois Créés »</em> : 4 emplois directs pérennes créés en zone rurale.</li>
                            </ul>
                        </li>
                        <li><strong>Enregistrement du Financement :</strong> Dans <em>Impact &gt; Financements</em>, enregistrement d'une subvention d'amorçage de <strong>12 000 000 FCFA</strong> allouée par le fonds FOA avec statut <em>« Décaissé »</em>.</li>
                        <li><strong>Dépôt Data Room :</strong> Dans <em>Impact &gt; Data Room</em>, Amadou téléverse les Statuts Notariés, le Registre de Commerce (RCCM) et le Pacte d'actionnaires confidentiel, classés dans la catégorie <em>« Juridique & Gouvernance »</em>.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 7 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50/50 p-5 dark:border-emerald-800/80 dark:bg-emerald-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°7 à insérer
                                </h4>
                                <span class="rounded bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200">
                                    Recommandation : 1440 &times; 800 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Vue de l'indicateur KPI avec l'onglet relationnel des relevés périodiques et le tableau des financements.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> La fiche d'indicateur avec les entrées mensuelles horodatées, et la ligne de financement décaissé pour BioAgri Tech.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ÉTAPE 8 DU CAS PRATIQUE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-gray-800">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-800 text-white font-bold text-sm">8</span>
                        <div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Étape 8 : Enquête Bilan, Centre d'Analyse & Rapport d'Impact pour le Bailleur
                            </h3>
                            <span class="text-xs text-teal-800 dark:text-teal-400 font-semibold">Menu Pilotage &gt; Centre d'analyse | Rapports & Exports</span>
                        </div>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                        Restitution & Clôture
                    </span>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <p>
                        Le programme s'achève par le Demo Day et la restitution au bailleur partenaire :
                    </p>
                    <ol class="list-decimal list-inside space-y-1.5 pl-2 text-xs">
                        <li><strong>Enquête de Satisfaction :</strong> Dans <em>Impact &gt; Formulaires & Enquêtes</em>, le responsable déverrouille le formulaire <em>« Bilan de Sortie Promo 2026 »</em> pour les fondateurs de la promotion.</li>
                        <li><strong>Visualisation Graphique :</strong> Dans <em>Pilotage &gt; Centre d'analyse</em>, consultation des KPI globaux : taux de complétion des livrables (94%), volume total de financements mobilisés (78M FCFA), CA cumulé des startups.</li>
                        <li><strong>Export Bailleurs :</strong> Dans <em>Pilotage &gt; Rapports</em>, le responsable génère l'export complet de la cohorte pour le bailleur <strong>FOA</strong>, certifiant les résultats réels et justifiant l'utilisation des subventions.</li>
                    </ol>
                </div>

                {{-- Boîte Image Placeholder 8 --}}
                <div class="relative overflow-hidden rounded-xl border-2 border-dashed border-teal-400 bg-teal-50/50 p-5 dark:border-teal-800/80 dark:bg-teal-950/20">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-teal-800 text-white shadow-sm">
                            <x-heroicon-o-camera class="h-6 w-6" />
                        </div>
                        <div class="flex-1 space-y-1">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900 dark:text-teal-300 flex items-center gap-1.5">
                                    📸 Capture d'Écran N°8 à insérer
                                </h4>
                                <span class="rounded bg-teal-100 px-2 py-0.5 text-[11px] font-semibold text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                    Recommandation : 1440 &times; 850 px &middot; PNG
                                </span>
                            </div>
                            <p class="text-xs font-semibold text-gray-900 dark:text-white">
                                Centre d'analyse affichant les graphiques de performance du portefeuille et le générateur de rapports.
                            </p>
                            <p class="text-[11px] text-gray-600 dark:text-gray-400">
                                <em>Contenu à capturer :</em> Les widgets de KPI consolidés, la courbe de progression financière et le bouton de téléchargement de l'export Excel/PDF.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 3 : GUIDE EXHAUSTIF DES ACTIONS PAR CLUSTER --}}
        <div class="space-y-6 pt-6">
            <div class="border-b border-gray-200 pb-3 dark:border-gray-800">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="h-6 w-6 text-teal-600" />
                    Répertoire Exhaustif des Actions par Cluster
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    Fiches pratiques et réflexes méthodologiques pour chaque module du SIGAE.
                </p>
            </div>

            {{-- Cluster 1 : Programmes & Sélection --}}
            <div id="cluster-programmes" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-indigo-600 text-white text-xs">
                        <x-heroicon-o-academic-cap class="h-5 w-5" />
                    </span>
                    1. Cluster « Programmes &amp; Sélection »
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                            Programmes &amp; Cohortes
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Définition des phases :</strong> Découper le parcours (nom, durée en semaines, ordre, critères de passage).</li>
                            <li>• <strong>Critères jury :</strong> Assigner coefficient et note maximale (sur 10 ou 20) par critère d'évaluation.</li>
                            <li>• <strong>Sélection obligatoire du formulaire :</strong> Chaque cohorte impose le choix d'un modèle de candidature (Standard Hub Africa ou Template personnalisé de la SAE).</li>
                            <li>• <strong>Synchronisation des cohortes :</strong> Bouton <em>« Synchroniser depuis le programme »</em> pour calculer automatiquement le rétro-planning complet de la promotion.</li>
                            <li>• <strong>Lien direct de candidature :</strong> Action <em>« Lien direct »</em> pour générer et copier l'URL d'inscription directe (`/cohortes/{id}/rejoindre`) à transmettre aux candidats (WhatsApp, email).</li>
                            <li>• <strong>Catalogue public & Cohortes ouvertes :</strong> Détection automatique du statut d'ouverture avec badge vert vif, passerelle de connexion/inscription et verrouillage sur la promotion.</li>
                            <li>• <strong>Onglets combinés au sommet :</strong> Accès direct aux phases réelles, séances &amp; émargements, candidatures et partenaires dès l'ouverture de la fiche.</li>
                            <li>• <strong>Matrice globale des présences (Excel) :</strong> Export en un clic du récapitulatif d'assiduité de toute la cohorte avec taux de présence calculé.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                            Jalons &amp; Phases (Nouveau Menu Direct)
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Vue centralisée :</strong> Consultation de l'ensemble des phases et jalons de toutes les cohortes confondues.</li>
                            <li>• <strong>Filtrage multicritère :</strong> Filtrer par cohorte, statut (À venir, En cours, Terminé) ou ordre chronologique.</li>
                            <li>• <strong>Ajustement rapide :</strong> Modifier à la volée les dates d'ouverture (`starts_on`) et d'échéance (`ends_on`).</li>
                            <li>• <strong>Projection calendrier :</strong> Chaque jalon alimente automatiquement le planning global de la SAE.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                            Candidatures, Notation Jury &amp; Espace Coach
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Vue unifiée 3 espaces :</strong> Fiche haute fidélité partagée (SAE, Coachs jurys, Candidats) avec stepper 4 jalons (*Soumission*, *Éligibilité*, *Jury*, *Délibération*).</li>
                            <li>• <strong>Actions de délibération d'en-tête :</strong> Boutons rapides *« Noter le dossier »*, *« Accepter »* (enrôlement direct en cohorte), *« Mettre en attente »*, *« Refuser »* (avec motif obligatoire) et *« Fiche projet »*.</li>
                            <li>• <strong>Portail Jurys Coachs :</strong> Espace dédié <code>/coach/{tenant}/applications</code> pour évaluer les dossiers avec la grille multicritères pondérée.</li>
                            <li>• <strong>Calcul pondéré &amp; Décision auto :</strong> Score sur 100 avec code couleur (vert &ge; 75, orange 50-74, rouge &lt; 50) et propagation automatique d'admission.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">
                            Partenaires &amp; Niveaux de Maturité
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Partenaires &amp; Bailleurs :</strong> Associer les financeurs aux programmes avec rôle et apport en FCFA.</li>
                            <li>• <strong>Paliers de maturité :</strong> Configurer les jalons de croissance (Idéation, MVP, Prototypage, Traction, Scale).</li>
                            <li>• <strong>Indicateurs d'étape :</strong> Mesurer le franchissement des niveaux par les startups du portefeuille.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Cluster 2 : Portefeuille Startups --}}
            <div id="cluster-portefeuille" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-sky-600 text-white text-xs">
                        <x-heroicon-o-briefcase class="h-5 w-5" />
                    </span>
                    2. Cluster « Portefeuille Startups »
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-sky-700 dark:text-sky-300 uppercase tracking-wide">
                            Fiche Projet 360° &amp; Traçabilité des Paliers
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Palier d'entrée &amp; Palier de sortie :</strong> Traçabilité automatique du niveau initial (<code>initial_level_id</code>) et du niveau final à la clôture (<code>exit_level_id</code>, <code>exited_at</code>).</li>
                            <li>• <strong>Onglet « Historique des Paliers » :</strong> Tableau chronologique des transitions de niveaux avec calcul automatique de la durée passée dans chaque jalon.</li>
                            <li>• <strong>Bouton « Promouvoir / Changer de palier » :</strong> Action rapide en table avec saisie obligatoire d'un motif et journalisation <em>MILESTONE</em> automatique.</li>
                            <li>• <strong>Coach référent &amp; Statut :</strong> Mentorat assigné, charge contrôlée et gestion des états (Actif, Diplômé, Sorti, Abandonné).</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-sky-700 dark:text-sky-300 uppercase tracking-wide">
                            Entrepreneurs &amp; Expérience Porteur 360°
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Unicité stricte du projet :</strong> 1 entrepreneur = 1 projet actif sur la plateforme avec redirection automatique directe.</li>
                            <li>• <strong>Page de détails immersive (`view-project`) :</strong> Hero card startup, métriques clés, blocs problème/solution et pitch elevator.</li>
                            <li>• <strong>Bloc « Parcours des Paliers » :</strong> Stepper dynamique (*Entrée &rarr; Actuel &rarr; Sortie*) et timeline des étapes franchies visible par le porteur.</li>
                            <li>• <strong>Timeline / Journal chronologique :</strong> Consigner les pivots, levées de fonds, passages de jalons et clôtures de parcours.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Cluster 3 : Accompagnement & Mentorat --}}
            <div id="cluster-accompagnement" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-teal-600 text-white text-xs">
                        <x-heroicon-o-user-group class="h-5 w-5" />
                    </span>
                    3. Cluster « Accompagnement &amp; Mentorat »
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-teal-700 dark:text-teal-300 uppercase tracking-wide">
                            Séances, Ateliers &amp; Émargement Hybride
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Typologie de séance :</strong> Séance individuelle (1-on-1), Atelier collectif de promotion, Revue de jalon.</li>
                            <li>• <strong>Peuplement Cohorte :</strong> Action 1-clic pour charger automatiquement toutes les startups de la promotion.</li>
                            <li>• <strong>Export/Import Excel :</strong> Extraction en CSV UTF-8 pré-rempli et ré-importation directe des présences et notes.</li>
                            <li>• <strong>Livrables d'émargement :</strong> Archivage du scan signé (PDF/Excel) dans l'onglet GED de la séance.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-teal-700 dark:text-teal-300 uppercase tracking-wide">
                            Calendrier &amp; Planning Global (Vue Unifiée)
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Projection multi-événements :</strong> Séances individuelles, ateliers, jalons de promotions (débuts &amp; échéances) et dates clés de cohortes.</li>
                            <li>• <strong>Filtres interactifs :</strong> Basculer par type (Tous, Séances, Jalons, Cohortes), cohorte spécifique ou coach.</li>
                            <li>• <strong>Modales interactives au clic :</strong> Affichage instantané du détail d'un jalon ou d'un atelier collectif.</li>
                            <li>• <strong>Navigation temporelle :</strong> Vue mensuelle fluide avec repérage immédiat de la date courante.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-teal-700 dark:text-teal-300 uppercase tracking-wide">
                            Livrables &amp; GED Polymorphe Versionnée
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Catalogue des attendus :</strong> Configurer les livrables obligatoires par niveau et par phase.</li>
                            <li>• <strong>Versionnage intelligent :</strong> Chaque dépôt incrémente la version (`v1`, `v2`, `v3`) et archive l'historique dans la GED.</li>
                            <li>• <strong>Processus de revue :</strong> Statuts <em>Soumis</em> &rarr; <em>Révisions requises</em> &rarr; <em>Validé</em> avec notation sur 20.</li>
                            <li>• <strong>Boîte à outils (Ressources) :</strong> Mettre à disposition guides, templates Excel de budget et trames de pitch deck.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-teal-700 dark:text-teal-300 uppercase tracking-wide">
                            Coachs, Mentors &amp; Liens d'Invitation
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Gestion du pool de mentors :</strong> Compétences clés, bio, capacité maximale de startups suivies.</li>
                            <li>• <strong>Invitations sécurisées :</strong> Générer un lien d'onboarding avec token unique (`/coach/invite/{token}`).</li>
                            <li>• <strong>Affectation simplifiée :</strong> Sélection rapide d'un coach disponible depuis la fiche projet ou séance.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Cluster 4 : Impact, Finance & Évaluation --}}
            <div id="cluster-impact" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-emerald-600 text-white text-xs">
                        <x-heroicon-o-chart-bar-square class="h-5 w-5" />
                    </span>
                    4. Cluster « Impact, Finance & Évaluation »
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">
                            Indicateurs S&E (KPI) & Financements
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Définition des métriques :</strong> Créer des indicateurs quantitatifs (CA, Emplois, Clients) et qualitatifs (Gouvernance).</li>
                            <li>• <strong>Relevés périodiques :</strong> Saisir les entrées datées (mensuelles, trimestrielles) dans l'onglet des valeurs.</li>
                            <li>• <strong>Financements :</strong> Tracer subventions, prêts d'honneur, prises de participation et décaissements réels.</li>
                            <li>• <strong>Justification bailleurs :</strong> Réconcilier les fonds engagés avec les performances mesurées.</li>
                        </ul>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-2">
                        <h4 class="font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">
                            Data Room & Formulaires d'Enquête
                        </h4>
                        <ul class="space-y-1.5 text-gray-600 dark:text-gray-300">
                            <li>• <strong>Data Room sécurisée :</strong> Stockage confidentiel des pièces légales (statuts, pacte, RIB, états financiers).</li>
                            <li>• <strong>Contrôle d'accès :</strong> Isolation étanche par SAE (TenantScope) et droits de consultation stricts.</li>
                            <li>• <strong>Modèles de Candidature & Enquêtes :</strong> Distinction claire entre templates officiels Hub Africa, templates de candidature SAE et enquêtes d'évaluation.</li>
                            <li>• <strong>Duplication 1-clic :</strong> Action pour cloner le template standard et concevoir rapidement des formulaires de candidature personnalisés.</li>
                            <li>• <strong>Formulaires sur-mesure :</strong> Créateur visuel de formulaires pour enquêtes de satisfaction et diagnostics.</li>
                            <li>• <strong>Déverrouillage ciblé :</strong> Ouvrir l'accès aux formulaires par cohorte avec date limite de réponse.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Module Transverse : Pilotage & Outils --}}
            <div id="module-pilotage" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 space-y-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-slate-700 text-white text-xs">
                        <x-heroicon-o-presentation-chart-line class="h-5 w-5" />
                    </span>
                    5. Module Transverse : « Pilotage, Décision & Audit »
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-1.5">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Centre d'Analyse
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Visualisation graphique en temps réel : répartition sectorielle, entonnoir de conversion des candidatures et taux de passage entre niveaux.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-1.5">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Rapports & Exports
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Génération de bilans de cohorte en PDF et fichiers Excel détaillés prêts à être transmis aux partenaires institutionnels et bailleurs de fonds.
                        </p>
                    </div>

                    <div class="rounded-lg border border-gray-100 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/40 space-y-1.5">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase tracking-wide">
                            Journal d'Audit
                        </h4>
                        <p class="text-gray-600 dark:text-gray-300">
                            Journal immuable enregistrant chaque action sensible (création, modification, notation, suppression) avec horodatage et adresse IP.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer d'aide --}}
        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-center text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400">
            Besoin d'un accompagnement personnalisé ou d'une formation d'équipe ? Contactez l'administrateur de votre SAE ou le support technique Hub Africa.
        </div>
    </div>
</x-filament-panels::page>
