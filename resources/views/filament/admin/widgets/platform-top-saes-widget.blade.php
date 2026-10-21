@php
    $saes = $this->getSaes();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-950 text-teal-600 dark:text-teal-400">
                        <x-heroicon-m-building-office-2 class="w-5 h-5" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            Structures d'Accompagnement (SAE) — Réseau Panafricain
                        </span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Incubateurs, accélérateurs et pôles d'innovation déployés sur l'infrastructure Hubs Africa
                        </p>
                    </div>
                </div>

                <x-filament::button
                    tag="a"
                    href="{{ url('/admin/saes') }}"
                    size="xs"
                    color="gray"
                    icon="heroicon-m-arrow-right"
                    icon-position="after"
                >
                    Gérer toutes les SAE
                </x-filament::button>
            </div>
        </x-slot>

        <div class="overflow-x-auto -mx-6 -mb-6">
            <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50/75 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-6 font-semibold">Structure (SAE)</th>
                        <th class="py-3 px-4 font-semibold">Typologie</th>
                        <th class="py-3 px-4 font-semibold">Localisation</th>
                        <th class="py-3 px-4 font-semibold text-center">Startups</th>
                        <th class="py-3 px-4 font-semibold text-center">Coachs</th>
                        <th class="py-3 px-4 font-semibold text-center">Cohortes</th>
                        <th class="py-3 px-4 font-semibold text-center">Statut</th>
                        <th class="py-3 px-6 font-semibold text-right">Espace SAE</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                    @forelse($saes as $s)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-800/40 transition">
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-primary-100 dark:bg-primary-950 flex items-center justify-center font-bold text-primary-700 dark:text-primary-300 text-xs shrink-0 overflow-hidden">
                                        @if($s['logo_url'])
                                            <img src="{{ $s['logo_url'] }}" alt="{{ $s['name'] }}" class="h-full w-full object-cover" />
                                        @else
                                            {{ strtoupper(substr($s['name'], 0, 2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white text-xs hover:text-primary-600 transition">
                                            {{ $s['name'] }}
                                        </p>
                                        <p class="text-[11px] text-gray-400 font-mono">
                                            /{{ $s['slug'] }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                    {{ $s['origin_label'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-500 dark:text-gray-400 truncate max-w-[180px]" title="{{ $s['location'] }}">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <span class="truncate">{{ $s['location'] }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-gray-900 dark:text-white">
                                <span class="inline-flex items-center justify-center min-w-[24px] px-1.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    {{ $s['projects_count'] }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-gray-600 dark:text-gray-300">
                                {{ $s['coaches_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-medium text-gray-600 dark:text-gray-300">
                                {{ $s['cohorts_count'] }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                    Actif
                                </span>
                            </td>
                            <td class="py-3.5 px-6 text-right">
                                <a
                                    href="{{ $s['url'] }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-700 dark:text-primary-400 hover:underline"
                                >
                                    <span>Accéder</span>
                                    <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-gray-400 text-xs">
                                Aucune structure enregistrée pour le moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
