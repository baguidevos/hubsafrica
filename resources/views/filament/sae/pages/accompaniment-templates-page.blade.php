<x-filament-panels::page>
    @php
        $templates = $this->templates;
    @endphp

    <div class="space-y-6">
        <!-- Bannière de présentation -->
        <div class="relative overflow-hidden rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-emerald-500" />
                    <span>Générateur Méthodologique en 1-Clic</span>
                </div>
                <h2 class="text-xl lg:text-2xl font-bold tracking-tight text-stone-900 dark:text-white">
                    Modèles d'Accompagnement & Guides Pédagogiques
                </h2>
                <p class="text-xs lg:text-sm text-stone-500 dark:text-stone-400 max-w-3xl leading-relaxed">
                    Déployez instantanément des programmes structurés et leurs parcours d'incubation. En un seul clic, l'ensemble des phases pédagogiques, livrables startups, livrables de gouvernance SAEI, critères de sélection et indicateurs M&E sont générés et connectés au tableau de bord.
                </p>
            </div>
        </div>

        <!-- Grille des Modèles Disponibles -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($templates as $key => $template)
                <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm p-6 flex flex-col justify-between hover:border-emerald-500/50 transition">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300">
                                <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-600" />
                                {{ $template->getBadge() }}
                            </span>
                            <span class="text-xs font-bold text-stone-400">
                                Durée : {{ $template->getDurationWeeks() }} semaines (6 mois)
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-stone-900 dark:text-white">
                                {{ $template->getTitle() }}
                            </h3>
                            <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5">
                                {{ $template->getSubtitle() }}
                            </p>
                            <p class="text-xs text-stone-600 dark:text-stone-400 mt-2 leading-relaxed">
                                {{ $template->getDescription() }}
                            </p>
                        </div>

                        <!-- Métriques incluses dans le pack -->
                        <div class="grid grid-cols-3 gap-3 pt-3 border-t border-stone-100 dark:border-stone-800 text-center">
                            <div class="p-2.5 rounded-xl bg-stone-50 dark:bg-stone-800/60">
                                <span class="block text-base font-black text-stone-900 dark:text-white">{{ count($template->getPhases()) }}</span>
                                <span class="text-[10px] font-medium text-stone-500 dark:text-stone-400">Jalons & Phases</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-stone-50 dark:bg-stone-800/60">
                                <span class="block text-base font-black text-emerald-600 dark:text-emerald-400">{{ count($template->getStartupDeliverables()) }}</span>
                                <span class="text-[10px] font-medium text-stone-500 dark:text-stone-400">Livrables Startups</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-stone-50 dark:bg-stone-800/60">
                                <span class="block text-base font-black text-indigo-600 dark:text-indigo-400">{{ count($template->getProgrammeDeliverables()) }}</span>
                                <span class="text-[10px] font-medium text-stone-500 dark:text-stone-400">Livrables SAEI</span>
                            </div>
                        </div>

                        <!-- Liste des 6 phases -->
                        <div class="space-y-2 pt-2">
                            <h4 class="text-xs font-bold text-stone-700 dark:text-stone-300 uppercase tracking-wider">
                                Parcours pédagogique :
                            </h4>
                            <div class="space-y-1.5 text-xs text-stone-600 dark:text-stone-400">
                                @foreach($template->getPhases() as $phase)
                                    <div class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                        <span><strong class="text-stone-800 dark:text-stone-200">{{ $phase['name'] }}</strong> ({{ $phase['duration_weeks'] }} sem.)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <span class="text-xs font-medium text-stone-400">Prêt au déploiement</span>
                        <button
                            type="button"
                            wire:click="mountAction('deploy', { template_key: '{{ $key }}', template_title: '{{ addslashes($template->getTitle()) }}' })"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition"
                        >
                            <x-heroicon-m-sparkles class="w-4 h-4 text-emerald-100" />
                            <span>Déployer ce modèle</span>
                        </button>
                    </div>
                </div>
            @endforeach

            <!-- Carte Emplacement pour le Second Guide -->
            <div class="rounded-2xl border-2 border-dashed border-stone-300 dark:border-stone-700 bg-stone-50/50 dark:bg-stone-800/30 p-6 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300">
                            <x-heroicon-m-clock class="w-4 h-4 text-purple-600" />
                            Emplacement Prêt — Guide N°2
                        </span>
                        <span class="text-xs font-bold text-stone-400">
                            Second Guide à intégrer
                        </span>
                    </div>

                    <div>
                        <h3 class="text-lg font-bold text-stone-900 dark:text-white">
                            Second Guide Méthodologique (En attente de vos fichiers)
                        </h3>
                        <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 mt-0.5">
                            Architecture extensible & plug-and-play
                        </p>
                        <p class="text-xs text-stone-600 dark:text-stone-400 mt-2 leading-relaxed">
                            Cet emplacement est configuré pour accueillir le deuxième guide d'accompagnement que vous allez fournir. Dès réception du document, son pack sera encapsulé dans un template dédié et déployable en un seul clic, avec ses propres livrables et jalons.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-purple-200 dark:border-purple-800/50 bg-purple-50/50 dark:bg-purple-950/20 text-xs text-purple-900 dark:text-purple-300 space-y-1.5">
                        <div class="font-bold flex items-center gap-1.5">
                            <x-heroicon-m-information-circle class="w-4 h-4 text-purple-600" />
                            <span>Comment ajouter le 2ᵉ guide :</span>
                        </div>
                        <p class="text-[11px] text-purple-700 dark:text-purple-300/80 leading-relaxed">
                            Fournissez simplement le document ou classeur Excel lors de votre prochain message. Le moteur créera automatiquement sa classe dérivée sans toucher aux autres fonctionnalités.
                        </p>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-stone-200 dark:border-stone-700/60 flex items-center justify-between">
                    <span class="text-xs font-medium text-stone-400">En attente des données</span>
                    <button
                        type="button"
                        disabled
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-stone-200 dark:bg-stone-700 text-stone-400 cursor-not-allowed"
                    >
                        <x-heroicon-m-lock-closed class="w-4 h-4" />
                        <span>Fournir le second guide</span>
                    </button>
                </div>
            </div>
        </div>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
