<x-filament-panels::page>
    @php
        $d = $this->getAnalyticsData();
    @endphp

    <div class="space-y-6">
        <!-- En-tête informatif -->
        <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-teal-600" />
                    Centre d'analyse & Diagnostic stratégique du portefeuille
                </h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 max-w-2xl">
                    Tableaux croisés de l'entonnoir d'admission, de l'inclusion démographique (parité genre, territoires), de l'intensité d'accompagnement et des volumes de financement mobilisés.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 shadow-sm transition">
                    <x-heroicon-o-printer class="w-4 h-4 text-stone-500" />
                    <span>Imprimer la synthèse</span>
                </button>
            </div>
        </div>

        <!-- 1. Entonnoir de sélection & Conversion -->
        <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm">
            <h4 class="text-sm font-bold text-stone-900 dark:text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <x-heroicon-m-funnel class="w-4 h-4 text-teal-600" />
                Entonnoir de sélection & taux de conversion
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($d['funnel'] as $idx => $step)
                    <div class="p-4 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-950/40 relative overflow-hidden">
                        <div class="text-[10px] font-bold text-stone-400 uppercase tracking-wider">Étape {{ $idx + 1 }}</div>
                        <div class="mt-1 text-2xl font-black text-stone-900 dark:text-white tabular-nums">
                            {{ $step['count'] }}
                        </div>
                        <div class="text-xs font-semibold text-stone-600 dark:text-stone-300 mt-0.5 truncate">
                            {{ $step['stage'] }}
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px] text-stone-500">
                            <span>Conversion globale</span>
                            <span class="font-bold text-teal-600 dark:text-teal-400">{{ $step['pct'] }}%</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 h-1.5 rounded-full mt-1.5 overflow-hidden">
                            <div class="bg-teal-600 h-1.5 rounded-full transition-all" style="width: {{ $step['pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 2. Parité genre & Inclusion territoriale -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Parité Genre -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-5 shadow-sm">
                <h4 class="text-sm font-bold text-stone-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-m-user-group class="w-4 h-4 text-teal-600" />
                    Parité Genre & Inclusion féminine
                </h4>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl border border-rose-100 dark:border-rose-950 bg-rose-50/30 dark:bg-rose-950/20">
                        <div>
                            <span class="text-xs font-bold text-rose-800 dark:text-rose-300">Femmes entrepreneures</span>
                            <p class="text-xl font-black text-rose-900 dark:text-rose-100 tabular-nums">{{ $d['femmes'] }}</p>
                        </div>
                        <span class="text-lg font-black text-rose-600 tabular-nums">{{ $d['pctFemmes'] }}%</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-100 dark:border-indigo-950 bg-indigo-50/30 dark:bg-indigo-950/20">
                        <div>
                            <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300">Hommes entrepreneurs</span>
                            <p class="text-xl font-black text-indigo-900 dark:text-indigo-100 tabular-nums">{{ $d['hommes'] }}</p>
                        </div>
                        <span class="text-lg font-black text-indigo-600 tabular-nums">{{ $d['pctHommes'] }}%</span>
                    </div>

                    <div class="w-full bg-indigo-500 h-3 rounded-full overflow-hidden flex shadow-inner">
                        <div class="bg-rose-500 h-3 transition-all" style="width: {{ $d['pctFemmes'] }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Territoires -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-5 shadow-sm">
                <h4 class="text-sm font-bold text-stone-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-m-map-pin class="w-4 h-4 text-teal-600" />
                    Ancrage territorial
                </h4>
                <div class="space-y-2.5">
                    @forelse($d['territoires'] as $t)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-700 dark:text-stone-300">{{ $t->territory }}</span>
                            <span class="font-bold text-stone-900 dark:text-white tabular-nums bg-stone-100 dark:bg-stone-800 px-2 py-0.5 rounded">{{ $t->count }} porteur(s)</span>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 py-4 text-center">Aucun territoire renseigné</p>
                    @endforelse
                </div>
            </div>

            <!-- Niveaux d'études -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-5 shadow-sm">
                <h4 class="text-sm font-bold text-stone-900 dark:text-white mb-3 flex items-center gap-2">
                    <x-heroicon-m-academic-cap class="w-4 h-4 text-teal-600" />
                    Niveaux de formation
                </h4>
                <div class="space-y-2.5">
                    @forelse($d['niveauxEtudes'] as $n)
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-stone-700 dark:text-stone-300">{{ $n->education_level }}</span>
                            <span class="font-bold text-stone-900 dark:text-white tabular-nums bg-stone-100 dark:bg-stone-800 px-2 py-0.5 rounded">{{ $n->count }}</span>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 py-4 text-center">Données académiques en cours de collecte</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Accompagnement & Financement -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Accompagnement -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-m-calendar-days class="w-4 h-4 text-teal-600" />
                        Intensité de l'accompagnement
                    </h4>
                    <span class="text-xs font-bold text-teal-600 bg-teal-50 dark:bg-teal-950/60 px-2.5 py-1 rounded-full">
                        {{ $d['totalHeures'] }} heures cumulées
                    </span>
                </div>

                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="p-3 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 text-center">
                        <span class="block text-[10px] text-stone-400 font-bold uppercase">Séances</span>
                        <span class="text-lg font-black text-stone-900 dark:text-white">{{ $d['totalSessions'] }}</span>
                    </div>
                    <div class="p-3 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 text-center">
                        <span class="block text-[10px] text-stone-400 font-bold uppercase">Volume horaire</span>
                        <span class="text-lg font-black text-teal-600">{{ $d['totalHeures'] }} h</span>
                    </div>
                    <div class="p-3 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 text-center">
                        <span class="block text-[10px] text-stone-400 font-bold uppercase">Moyenne/séance</span>
                        <span class="text-lg font-black text-stone-900 dark:text-white">60 min</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-semibold text-stone-600 dark:text-stone-300 block mb-1">Ventilation par format d'intervention :</span>
                    @foreach($d['typesSeance'] as $type => $count)
                        <div class="flex items-center justify-between text-xs p-2 rounded-lg border border-stone-100 dark:border-stone-800">
                            <span class="font-medium text-stone-700 dark:text-stone-300">{{ $type }}</span>
                            <span class="font-bold text-stone-900 dark:text-white">{{ $count }} séance(s)</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Financements -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-stone-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-m-banknotes class="w-4 h-4 text-emerald-600" />
                        Financements & Fonds mobilisés
                    </h4>
                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/60 px-2.5 py-1 rounded-full">
                        {{ number_format($d['totalFinancement'], 0, ',', ' ') }} FCFA
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($d['financements'] as $f)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-stone-100 dark:border-stone-800 bg-stone-50/50 dark:bg-stone-950/40">
                            <span class="text-xs font-semibold text-stone-800 dark:text-stone-200">{{ $f->type instanceof \App\Enums\FundingType ? $f->type->getLabel() : ($f->type ?: 'Autre source') }}</span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {{ number_format((float) $f->total, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    @empty
                        <div class="p-6 border border-dashed rounded-xl border-stone-200 dark:border-stone-800 text-center">
                            <p class="text-xs text-stone-400">Aucun enregistrement de levée validé pour le moment.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
