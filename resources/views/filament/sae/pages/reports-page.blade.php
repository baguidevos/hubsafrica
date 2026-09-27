<x-filament-panels::page>
    @php
        $indicators = $this->getIndicatorsData();
    @endphp

    <div class="space-y-6">
        <!-- En-tête et filtres -->
        <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-stone-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-document-chart-bar class="w-6 h-6 text-teal-600" />
                    Rapports d'impact & Dispositif de Suivi-Évaluation CUBE
                </h3>
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 max-w-2xl">
                    Consolidation multi-cohortes des indicateurs de gouvernance, d'accompagnement, d'insertion et de mobilisation de fonds.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <button wire:click="exportCsv" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-700 shadow-sm transition">
                    <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-teal-600" />
                    <span>Export CSV</span>
                </button>
                <button onclick="window.print()" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-teal-600 text-white hover:bg-teal-700 shadow-sm transition">
                    <x-heroicon-o-printer class="w-4 h-4" />
                    <span>Imprimer</span>
                </button>
            </div>
        </div>

        <!-- Filtres par programme et cohorte -->
        <div class="flex flex-wrap items-center gap-4 p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900">
            <div class="flex items-center gap-2 text-xs">
                <label class="font-medium text-stone-600 dark:text-stone-400">Programme :</label>
                <select wire:model.live="selectedProgramId" class="text-xs rounded-lg border-stone-300 dark:border-stone-700 dark:bg-stone-800 text-stone-800 dark:text-stone-200 py-1.5 px-3">
                    <option value="all">Tous les programmes</option>
                    @foreach($this->programs as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <label class="font-medium text-stone-600 dark:text-stone-400">Cohorte :</label>
                <select wire:model.live="selectedCohortId" class="text-xs rounded-lg border-stone-300 dark:border-stone-700 dark:bg-stone-800 text-stone-800 dark:text-stone-200 py-1.5 px-3">
                    <option value="all">Toutes les cohortes</option>
                    @foreach($this->cohorts as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table des indicateurs CUBE -->
        <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-200 dark:border-stone-800">
                <h4 class="text-sm font-bold text-stone-900 dark:text-white uppercase tracking-wider">
                    Cadre de mesure des résultats
                </h4>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-stone-50 dark:bg-stone-950 border-b border-stone-200 dark:border-stone-800 text-stone-500 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="px-4 py-3">Indicateur</th>
                            <th class="px-4 py-3">Catégorie</th>
                            <th class="px-4 py-3">Formule de calcul</th>
                            <th class="px-4 py-3 text-right">Valeur actuelle</th>
                            <th class="px-4 py-3 text-right">Cible CUBE</th>
                            <th class="px-4 py-3 text-center">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200 dark:divide-stone-800">
                        @foreach($indicators as $ind)
                            @php
                                $statusBadge = match($ind['status']) {
                                    'ATTEINT' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800',
                                    'VIGILANCE' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200 dark:border-amber-800',
                                    default => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800',
                                };
                            @endphp
                            <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/50 transition">
                                <td class="px-4 py-3 font-semibold text-stone-900 dark:text-white">
                                    {{ $ind['name'] }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300">
                                        {{ $ind['category'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-stone-500 font-mono text-[11px]">
                                    {{ $ind['formula'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-stone-900 dark:text-white tabular-nums">
                                    {{ is_numeric($ind['current']) ? number_format($ind['current'], 0, ',', ' ') : $ind['current'] }} {{ $ind['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-stone-500 tabular-nums">
                                    {{ is_numeric($ind['target']) ? number_format($ind['target'], 0, ',', ' ') : $ind['target'] }} {{ $ind['unit'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $statusBadge }}">
                                        {{ $ind['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
