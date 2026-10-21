@php
    $kpi = $this->getKpiData();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-950 text-primary-600 dark:text-primary-400">
                        <x-heroicon-m-chart-bar-square class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            Mesures de Traction & Ratios d'Impact Panafricains — Hubs Africa
                        </span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Indicateurs de performance, de parité et d'intensité de l'accompagnement consolidés en temps réel
                        </p>
                    </div>
                </div>

                {{-- Badges ODD --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        <x-heroicon-m-check-badge class="w-3.5 h-3.5" />
                        ODD 8 : Travail Décent
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        <x-heroicon-m-sparkles class="w-3.5 h-3.5" />
                        ODD 5 : Genre & Parité
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        <x-heroicon-m-globe-alt class="w-3.5 h-3.5" />
                        ODD 9 : Tech Panafricaine
                    </span>
                </div>
            </div>
        </x-slot>

        {{-- Grille des 6 Ratios Clés de Traction --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 pt-2">

            {{-- 1. Féminisation --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Féminisation</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">ODD 5</span>
                    </div>
                    <div class="mt-2 text-2xl font-black tabular-nums text-amber-600 dark:text-amber-400">
                        {{ $kpi['female_rate'] }}%
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                    {{ $kpi['female_count'] }} femmes dirigeantes
                </div>
            </div>

            {{-- 2. Taux de Rétention / Survie --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Taux de Survie</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">Pérennité</span>
                    </div>
                    <div class="mt-2 text-2xl font-black tabular-nums text-emerald-600 dark:text-emerald-400">
                        {{ $kpi['retention_rate'] }}%
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                    Projets maintenus actifs
                </div>
            </div>

            {{-- 3. Sélectivité des Jurys --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Sélectivité</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-primary-100 dark:bg-primary-900/60 text-primary-700 dark:text-primary-300">Admission</span>
                    </div>
                    <div class="mt-2 text-2xl font-black tabular-nums text-primary-600 dark:text-primary-400">
                        {{ $kpi['admission_rate'] }}%
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                    Taux d'admission aux jurys
                </div>
            </div>

            {{-- 4. Intensité Mentorat --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Ratio Mentorat</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300">Qualité</span>
                    </div>
                    <div class="mt-2 text-2xl font-black tabular-nums text-indigo-600 dark:text-indigo-400">
                        {{ $kpi['mentoring_ratio'] }}x
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                    Coachs par startup active
                </div>
            </div>

            {{-- 5. Couverture Géographique --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Présence Régionale</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300">Afrique</span>
                    </div>
                    <div class="mt-2 text-2xl font-black tabular-nums text-blue-600 dark:text-blue-400">
                        {{ $kpi['country_count'] }} pays
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5 truncate" title="{{ $kpi['countries_list'] }}">
                    {{ $kpi['countries_list'] }}
                </div>
            </div>

            {{-- 6. Ticket Moyen Décaissé --}}
            <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 shadow-xs transition hover:shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Ticket Moyen</span>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300">Levée</span>
                    </div>
                    <div class="mt-2 text-base font-black tabular-nums text-emerald-600 dark:text-emerald-400 truncate" title="{{ $kpi['average_ticket'] }}">
                        {{ $kpi['average_ticket'] }}
                    </div>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-gray-800 pt-1.5">
                    Par projet soutenu
                </div>
            </div>

        </div>
    </x-filament::section>
</x-filament-widgets::widget>
