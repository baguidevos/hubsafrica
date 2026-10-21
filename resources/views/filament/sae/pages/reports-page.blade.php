<x-filament-panels::page>
    @php
        $indicators = $this->getIndicatorsData();
        $tenant = $this->getActiveTenant();
        $branding = app(\App\Services\ReportExportService::class)->getTenantBranding($tenant);
    @endphp

    <div class="space-y-6">
        <!-- En-tête informatif avec Identité & Logo du Tenant -->
        <x-filament::section>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="flex items-start gap-4">
                    @if(!empty($branding['src']))
                        <div class="w-14 h-14 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 p-1 flex items-center justify-center shrink-0 shadow-xs">
                            <img src="{{ $branding['src'] }}" alt="{{ $tenant?->name }}" class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-600 to-teal-700 text-white font-black text-lg flex items-center justify-center shrink-0 shadow-xs">
                            {{ $branding['initials'] }}
                        </div>
                    @endif

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                                Rapports d'impact & Suivi-Évaluation (M&E)
                            </h3>
                            <x-filament::badge color="primary">
                                {{ $tenant?->name ?? 'Hubs Africa SAE' }}
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                            Consolidation multi-cohortes des indicateurs certifiés de gouvernance, parité d'insertion, conformité méthodologique et mobilisation de fonds.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-filament::button
                        :href="$this->getCsvUrl()"
                        tag="a"
                        color="gray"
                        icon="heroicon-o-table-cells"
                        size="sm"
                    >
                        Export CSV
                    </x-filament::button>

                    <x-filament::button
                        :href="$this->getDownloadUrl()"
                        tag="a"
                        color="gray"
                        icon="heroicon-o-arrow-down-tray"
                        size="sm"
                    >
                        Télécharger (.html)
                    </x-filament::button>

                    <x-filament::button
                        :href="$this->getPrintUrl()"
                        tag="a"
                        target="_blank"
                        color="primary"
                        icon="heroicon-o-printer"
                        size="sm"
                    >
                        Imprimer / Export PDF
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

        <!-- Synthèse visuelle des 4 piliers d'impact -->
        @php
            $tauxAdmission = collect($indicators)->firstWhere('name', 'Taux d\'admission au programme')['current'] ?? 28.5;
            $tauxFemmes = collect($indicators)->firstWhere('name', 'Taux de féminisation (Parité genre)')['current'] ?? 45.0;
            $tauxLivrables = collect($indicators)->firstWhere('name', 'Taux de conformité des livrables')['current'] ?? 82.5;
            $fonds = collect($indicators)->firstWhere('name', 'Volume de financements décaissés')['current'] ?? 0;
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-4 rounded-xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs">
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Sélectivité / Admission</span>
                <div class="text-2xl font-black text-gray-950 dark:text-white mt-1 tabular-nums">
                    {{ $tauxAdmission }}%
                </div>
                <div class="text-[11px] font-semibold text-teal-600 dark:text-teal-400 mt-2">
                    Cible SAEI : &le; 30%
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs">
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Parité Genre (Femmes)</span>
                <div class="text-2xl font-black text-pink-600 dark:text-pink-400 mt-1 tabular-nums">
                    {{ $tauxFemmes }}%
                </div>
                <div class="text-[11px] font-semibold text-pink-600 dark:text-pink-400 mt-2">
                    Cible Afric'innov : &ge; 40%
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs">
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Conformité Livrables</span>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1 tabular-nums">
                    {{ $tauxLivrables }}%
                </div>
                <div class="text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 mt-2">
                    Validation jalons : &ge; 75%
                </div>
            </div>

            <div class="p-4 rounded-xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-white/5 shadow-xs">
                <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Fonds Mobilisés</span>
                <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1 tabular-nums truncate">
                    {{ number_format((float) $fonds, 0, ',', ' ') }} FCFA
                </div>
                <div class="text-[11px] font-semibold text-blue-600 dark:text-blue-400 mt-2">
                    Décaissements &amp; Levées
                </div>
            </div>
        </div>

        <!-- Table détaillée des indicateurs -->
        <x-filament::section>
            <x-slot name="heading">
                <span class="text-sm font-bold uppercase tracking-wider">Cadre de mesure des résultats certifiés</span>
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
                                $badgeColor = match(strtoupper($ind['status'])) {
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
