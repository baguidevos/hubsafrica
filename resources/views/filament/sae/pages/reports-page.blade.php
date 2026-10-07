<x-filament-panels::page>
    @php
        $indicators = $this->getIndicatorsData();
    @endphp

    <div class="space-y-6">
        <!-- En-tête informatif avec support du thème Filament -->
        <x-filament::section>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-document-chart-bar class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                            Rapports d'impact & Dispositif de Suivi-Évaluation
                        </h3>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                        Consolidation multi-cohortes des indicateurs de gouvernance, d'accompagnement, d'insertion et de mobilisation de fonds.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button
                        wire:click="exportCsv"
                        color="gray"
                        icon="heroicon-o-arrow-down-tray"
                        size="sm"
                        type="button"
                    >
                        Export CSV
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        icon="heroicon-o-printer"
                        size="sm"
                        onclick="window.print()"
                        type="button"
                    >
                        Imprimer
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>

        <!-- Filtres par programme et cohorte -->
        <x-filament::section compact>
            <div class="flex flex-wrap items-center gap-6">
                <div class="flex items-center gap-2 text-xs">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Programme :</label>
                    <select
                        wire:model.live="selectedProgramId"
                        class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">Tous les programmes</option>
                        @foreach($this->programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Cohorte :</label>
                    <select
                        wire:model.live="selectedCohortId"
                        class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    >
                        <option value="all">Toutes les cohortes</option>
                        @foreach($this->cohorts as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        <!-- Table des indicateurs -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-sm font-bold uppercase tracking-wider">Cadre de mesure des résultats</span>
            </x-slot>

            <div class="overflow-x-auto -mx-6 -mb-6">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50/80 dark:bg-white/5 border-b border-gray-200/70 dark:border-white/10 text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-4 py-3">Indicateur</th>
                            <th class="px-4 py-3">Catégorie</th>
                            <th class="px-4 py-3">Formule de calcul</th>
                            <th class="px-4 py-3 text-right">Valeur actuelle</th>
                            <th class="px-4 py-3 text-right">Cible</th>
                            <th class="px-4 py-3 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($indicators as $ind)
                            @php
                                $badgeColor = match($ind['status']) {
                                    'ATTEINT' => 'success',
                                    'VIGILANCE' => 'warning',
                                    default => 'danger',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                    {{ $ind['name'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <x-filament::badge color="gray">
                                        {{ $ind['category'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-[11px]">
                                    {{ $ind['formula'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-gray-950 dark:text-white tabular-nums">
                                    {{ is_numeric($ind['current']) ? number_format($ind['current'], 0, ',', ' ') : $ind['current'] }} {{ $ind['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-500 dark:text-gray-400 tabular-nums">
                                    {{ is_numeric($ind['target']) ? number_format($ind['target'], 0, ',', ' ') : $ind['target'] }} {{ $ind['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-filament::badge :color="$badgeColor">
                                        {{ $ind['status'] }}
                                    </x-filament::badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
