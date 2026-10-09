<x-filament-panels::page>
    @php
        $templates = $this->templates;
    @endphp

    <div class="space-y-6">
        <!-- Bannière de présentation native Filament -->
        <x-filament::section>
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-emerald-500" />
                    <span>Générateur Méthodologique en 1-Clic</span>
                </div>
                <h2 class="text-xl lg:text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                    Modèles d'Accompagnement & Guides Pédagogiques
                </h2>
                <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 max-w-3xl leading-relaxed">
                    Déployez instantanément des programmes structurés et leurs parcours d'incubation. En un seul clic, l'ensemble des phases pédagogiques, livrables startups, livrables de gouvernance SAEI, critères de sélection et indicateurs M&E sont générés et connectés au tableau de bord.
                </p>
            </div>
        </x-filament::section>

        <!-- Grille des Modèles Disponibles -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($templates as $key => $template)
                <x-filament::section class="flex flex-col justify-between hover:ring-1 hover:ring-primary-500/50 transition">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <x-filament::badge color="success" icon="heroicon-m-check-badge">
                                {{ $template->getBadge() }}
                            </x-filament::badge>
                            <span class="text-xs font-bold text-gray-400 dark:text-gray-500">
                                Durée : {{ $template->getDurationWeeks() }} semaines (6 mois)
                            </span>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                                {{ $template->getTitle() }}
                            </h3>
                            <p class="text-xs font-semibold text-primary-600 dark:text-primary-400 mt-0.5">
                                {{ $template->getSubtitle() }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-2 leading-relaxed">
                                {{ $template->getDescription() }}
                            </p>
                        </div>

                        <!-- Métriques incluses dans le pack -->
                        <div class="grid grid-cols-3 gap-3 pt-3 border-t border-gray-100 dark:border-white/5 text-center">
                            <div class="p-2.5 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                                <span class="block text-base font-black text-gray-950 dark:text-white">{{ count($template->getPhases()) }}</span>
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Jalons & Phases</span>
                            </div>
                            <div class="p-2.5 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                                <span class="block text-base font-black text-emerald-600 dark:text-emerald-400">{{ count($template->getStartupDeliverables()) }}</span>
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Livrables Startups</span>
                            </div>
                            <div class="p-2.5 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                                <span class="block text-base font-black text-indigo-600 dark:text-indigo-400">{{ count($template->getProgrammeDeliverables()) }}</span>
                                <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400">Livrables SAEI</span>
                            </div>
                        </div>

                        <!-- Liste des 6 phases -->
                        <div class="space-y-2 pt-2">
                            <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                Parcours pédagogique :
                            </h4>
                            <div class="space-y-1.5 text-xs text-gray-600 dark:text-gray-400">
                                @foreach($template->getPhases() as $phase)
                                    <div class="flex items-start gap-2">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mt-1.5 shrink-0"></span>
                                        <span><strong class="text-gray-900 dark:text-gray-100">{{ $phase['name'] }}</strong> ({{ $phase['duration_weeks'] }} sem.)</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center justify-between gap-2">
                        <a
                            href="{{ \App\Filament\Sae\Pages\AccompanimentTemplateDetailPage::getUrl(['templateKey' => $key]) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:text-gray-950 bg-gray-100 hover:bg-gray-200 dark:text-gray-300 dark:bg-white/5 dark:hover:bg-white/10 transition"
                        >
                            <x-heroicon-m-book-open class="w-3.5 h-3.5 text-primary-500" />
                            <span>Guide A-Z &amp; Liens Drive</span>
                        </a>

                        <x-filament::button
                            wire:click="mountAction('deploy', { template_key: '{{ $key }}', template_title: '{{ addslashes($template->getTitle()) }}' })"
                            color="primary"
                            icon="heroicon-m-sparkles"
                            size="sm"
                            type="button"
                        >
                            Déployer ce modèle
                        </x-filament::button>
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Notice d'extensibilité de la bibliothèque --}}
        <x-filament::section compact>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-check-badge class="w-4 h-4 text-emerald-500 shrink-0" />
                    <span>
                        <strong class="text-gray-900 dark:text-white">2 Méthodologies Officielles Actives :</strong>
                        <strong>Startup Loop</strong> (Afric'innov / GIZ) et <strong>Méthodologie CUBE</strong> (SAEI CUBE &amp; KPITAL INVEST).
                    </span>
                </div>
                <div class="inline-flex items-center gap-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                    <x-heroicon-m-information-circle class="w-3.5 h-3.5" />
                    <span>Déploiement 1-clic : phases, livrables, critères jury et indicateurs M&amp;E synchronisés.</span>
                </div>
            </div>
        </x-filament::section>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
