<x-filament-panels::page>
    @php
        $d = $this->getAnalyticsData();
        $countryBreakdown = $this->getCountryBreakdown();
        $tenant = $this->getActiveTenant();
        $branding = app(\App\Services\ReportExportService::class)->getTenantBranding($tenant);
    @endphp

    <div class="space-y-6">
        <!-- En-tête informatif avec Logo & Identité du Tenant -->
        <x-filament::section>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="flex items-start gap-4">
                    @if(!empty($branding['src']))
                        <div class="w-14 h-14 rounded-xl border border-gray-200 dark:border-white/10 p-1 flex items-center justify-center shrink-0 shadow-xs">
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
                                Centre d'analyse & Diagnostic stratégique du portefeuille
                            </h3>
                            <x-filament::badge color="primary">
                                {{ $tenant?->name ?? 'Hubs Africa SAE' }}
                            </x-filament::badge>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-2xl">
                            Tableaux croisés de l'entonnoir d'admission, de l'inclusion démographique (parité genre, territoires), de l'intensité d'accompagnement et des volumes de financement mobilisés.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <x-filament::button
                        wire:click="exportCountryBreakdownCsv"
                        color="success"
                        icon="heroicon-o-globe-europe-africa"
                        size="sm"
                    >
                        Export Pays &amp; Appels (Excel)
                    </x-filament::button>

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
                    <select wire:model.live="selectedProgramId"
                        class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Tous les programmes</option>
                        @foreach($this->programs as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Cohorte :</label>
                    <select wire:model.live="selectedCohortId"
                        class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Toutes les cohortes</option>
                        @foreach($this->cohorts as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filament::section>

        <!-- 1. Entonnoir de sélection & Conversion -->
        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider">
                    <x-heroicon-m-funnel class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                    <span>Entonnoir de sélection & taux de conversion</span>
                </div>
            </x-slot>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($d['funnel'] as $idx => $step)
                    <div class="p-4 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 relative overflow-hidden">
                        <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Étape {{ $idx + 1 }}</div>
                        <div class="mt-1 text-2xl font-black text-gray-950 dark:text-white tabular-nums">
                            {{ $step['count'] }}
                        </div>
                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 mt-0.5 truncate">
                            {{ $step['stage'] }}
                        </div>
                        <div class="mt-3 flex items-center justify-between text-[11px] text-gray-500 dark:text-gray-400">
                            <span>Conversion globale</span>
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ $step['pct'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 h-1.5 rounded-full mt-1.5 overflow-hidden">
                            <div class="bg-primary-600 dark:bg-primary-500 h-1.5 rounded-full transition-all" style="width: {{ $step['pct'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>

        <!-- 2. Parité genre & Inclusion territoriale -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Parité Genre -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <x-heroicon-m-user-group class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Parité Genre & Inclusion féminine</span>
                    </div>
                </x-slot>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl border border-rose-200/60 dark:border-rose-900/40 bg-rose-50/40 dark:bg-rose-950/20">
                        <div>
                            <span class="text-xs font-bold text-rose-800 dark:text-rose-300">Femmes entrepreneures</span>
                            <p class="text-xl font-black text-rose-900 dark:text-rose-100 tabular-nums">{{ $d['femmes'] }}</p>
                        </div>
                        <span class="text-lg font-black text-rose-600 dark:text-rose-400 tabular-nums">{{ $d['pctFemmes'] }}%</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-200/60 dark:border-indigo-900/40 bg-indigo-50/40 dark:bg-indigo-950/20">
                        <div>
                            <span class="text-xs font-bold text-indigo-800 dark:text-indigo-300">Hommes entrepreneurs</span>
                            <p class="text-xl font-black text-indigo-900 dark:text-indigo-100 tabular-nums">{{ $d['hommes'] }}</p>
                        </div>
                        <span class="text-lg font-black text-indigo-600 dark:text-indigo-400 tabular-nums">{{ $d['pctHommes'] }}%</span>
                    </div>

                    <div class="w-full bg-indigo-500/80 h-3 rounded-full overflow-hidden flex shadow-inner">
                        <div class="bg-rose-500 h-3 transition-all" style="width: {{ $d['pctFemmes'] }}%"></div>
                    </div>
                </div>
            </x-filament::section>

            <!-- Territoires -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <x-heroicon-m-map-pin class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Ancrage territorial</span>
                    </div>
                </x-slot>

                <div class="space-y-2.5">
                    @forelse($d['territoires'] as $t)
                        <div class="flex items-center justify-between text-xs p-2 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $t->territory }}</span>
                            <x-filament::badge color="gray">
                                {{ $t->count }} porteur(s)
                            </x-filament::badge>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 py-4 text-center">Aucun territoire renseigné</p>
                    @endforelse
                </div>
            </x-filament::section>

            <!-- Niveaux d'études -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <x-heroicon-m-academic-cap class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Niveaux de formation</span>
                    </div>
                </x-slot>

                <div class="space-y-2.5">
                    @forelse($d['niveauxEtudes'] as $n)
                        <div class="flex items-center justify-between text-xs p-2 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $n->education_level }}</span>
                            <x-filament::badge color="gray">
                                {{ $n->count }}
                            </x-filament::badge>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 py-4 text-center">Données académiques en cours de collecte</p>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <!-- 3. Accompagnement & Financement -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Accompagnement -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <x-heroicon-m-calendar-days class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                        <span>Intensité de l'accompagnement</span>
                    </div>
                </x-slot>

                <x-slot name="headerEnd">
                    <x-filament::badge color="primary">
                        {{ $d['totalHeures'] }} heures cumulées
                    </x-filament::badge>
                </x-slot>

                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Séances</span>
                        <span class="text-lg font-black text-gray-950 dark:text-white">{{ $d['totalSessions'] }}</span>
                    </div>
                    <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Volume horaire</span>
                        <span class="text-lg font-black text-primary-600 dark:text-primary-400">{{ $d['totalHeures'] }} h</span>
                    </div>
                    <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase">Moyenne/séance</span>
                        <span class="text-lg font-black text-gray-950 dark:text-white">60 min</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-400 block mb-1">Ventilation par format d'intervention :</span>
                    @foreach($d['typesSeance'] as $type => $count)
                        <div class="flex items-center justify-between text-xs p-2.5 rounded-lg border border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/5">
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $type }}</span>
                            <span class="font-bold text-gray-950 dark:text-white">{{ $count }} séance(s)</span>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>

            <!-- Financements -->
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2 text-sm font-bold">
                        <x-heroicon-m-banknotes class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                        <span>Financements & Fonds mobilisés</span>
                    </div>
                </x-slot>

                <x-slot name="headerEnd">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge color="success">
                            {{ number_format($d['totalFinancement'], 0, ',', ' ') }} FCFA décaissés
                        </x-filament::badge>
                        @if(($d['totalFinancementAccorde'] ?? 0) > 0)
                            <x-filament::badge color="info">
                                {{ number_format($d['totalFinancementAccorde'], 0, ',', ' ') }} FCFA accordés
                            </x-filament::badge>
                        @endif
                    </div>
                </x-slot>

                <div class="space-y-3">
                    @forelse($d['financements'] as $f)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5">
                            <span class="text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $f->type instanceof \App\Enums\FundingType ? $f->type->getLabel() : ($f->type ?: 'Autre source') }}</span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">
                                {{ number_format((float) $f->total, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                    @empty
                        <div class="p-6 border border-dashed rounded-xl border-gray-200 dark:border-white/10 text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Aucun enregistrement de levée validé pour le moment.</p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>
        </div>

        <!-- Répartition Géographique par Pays & par Appel -->
        @php
            $cbSummary = $countryBreakdown['summary'] ?? [];
            $globalByCountry = $countryBreakdown['global_by_country'] ?? [];
            $byCallAndCountry = $countryBreakdown['by_call_and_country'] ?? [];
        @endphp

        <x-filament::section>
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-sm font-bold">
                    <x-heroicon-m-globe-europe-africa class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                    <span>Répartition géographique par pays &amp; par appel</span>
                </div>
            </x-slot>

            <x-slot name="headerEnd">
                <div class="flex flex-wrap items-center gap-2">
                    <x-filament::badge color="primary">
                        {{ $cbSummary['total_accounts'] ?? 0 }} comptes au total
                    </x-filament::badge>
                    <x-filament::badge color="warning">
                        {{ $cbSummary['accounts_without_project'] ?? 0 }} sans projet
                    </x-filament::badge>
                    <x-filament::badge color="info">
                        {{ $cbSummary['total_applications'] ?? 0 }} candidatures
                    </x-filament::badge>
                    <x-filament::button
                        color="success"
                        size="xs"
                        icon="heroicon-m-table-cells"
                        wire:click="exportCountryBreakdownCsv"
                    >
                        Exporter CSV / Excel
                    </x-filament::button>
                </div>
            </x-slot>

            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                Le pays est résolu depuis le <strong>compte entrepreneur</strong> en priorité, puis en repli depuis le formulaire de candidature si non renseigné sur le compte. La répartition globale inclut tous les comptes inscrits, y compris les comptes sans projet.
            </p>

            <!-- Mini synthèse -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
                <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Total comptes</span>
                    <span class="text-lg font-black text-gray-950 dark:text-white tabular-nums">{{ $cbSummary['total_accounts'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Avec candidature</span>
                    <span class="text-lg font-black text-primary-600 dark:text-primary-400 tabular-nums">{{ $cbSummary['accounts_with_application'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Avec projet</span>
                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $cbSummary['accounts_with_project'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Sans projet</span>
                    <span class="text-lg font-black text-amber-600 dark:text-amber-400 tabular-nums">{{ $cbSummary['accounts_without_project'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-center">
                    <span class="block text-[10px] text-gray-500 dark:text-gray-400 font-bold uppercase">Total candidatures</span>
                    <span class="text-lg font-black text-sky-600 dark:text-sky-400 tabular-nums">{{ $cbSummary['total_applications'] ?? 0 }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Tableau 1 : Répartition globale par pays -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        1. Répartition globale par pays (tous les comptes, y compris sans projet)
                    </h4>

                    @if(count($globalByCountry) > 0)
                        <div class="overflow-x-auto rounded-xl border border-gray-200/70 dark:border-white/10">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 uppercase text-[10px]">
                                    <tr>
                                        <th class="px-3 py-2.5 font-bold">Pays</th>
                                        <th class="px-3 py-2.5 font-bold text-right">Total</th>
                                        <th class="px-3 py-2.5 font-bold text-right">Candidatures</th>
                                        <th class="px-3 py-2.5 font-bold text-right">Avec projet</th>
                                        <th class="px-3 py-2.5 font-bold text-right">Sans projet</th>
                                        <th class="px-3 py-2.5 font-bold text-right">Part</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                    @foreach($globalByCountry as $row)
                                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5">
                                            <td class="px-3 py-2 font-semibold text-gray-900 dark:text-white">{{ $row['country'] }}</td>
                                            <td class="px-3 py-2 text-right font-bold text-gray-900 dark:text-white tabular-nums">{{ $row['total_accounts'] }}</td>
                                            <td class="px-3 py-2 text-right text-primary-600 dark:text-primary-400 tabular-nums">{{ $row['with_application'] }}</td>
                                            <td class="px-3 py-2 text-right text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $row['with_project'] }}</td>
                                            <td class="px-3 py-2 text-right text-amber-600 dark:text-amber-400 tabular-nums">{{ $row['without_project'] }}</td>
                                            <td class="px-3 py-2 text-right text-gray-500 dark:text-gray-400 tabular-nums">{{ number_format($row['percentage'], 1, ',', '') }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 border border-dashed rounded-xl border-gray-200 dark:border-white/10 text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Aucun compte entrepreneur enregistré pour le moment.</p>
                        </div>
                    @endif
                </div>

                <!-- Tableau 2 : Répartition par appel × pays -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-3">
                        2. Répartition par appel (Programme / Cohorte) × Pays
                    </h4>

                    @if(count($byCallAndCountry) > 0)
                        <div class="space-y-3">
                            @foreach($byCallAndCountry as $call)
                                <div class="rounded-xl border border-gray-200/70 dark:border-white/10 overflow-hidden">
                                    <div class="flex items-center justify-between px-3 py-2 bg-gray-50 dark:bg-white/5 border-b border-gray-200/60 dark:border-white/10">
                                        <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $call['call_label'] }}</span>
                                        <x-filament::badge color="primary" size="sm">
                                            {{ $call['total_applications'] }} candidature(s)
                                        </x-filament::badge>
                                    </div>
                                    <table class="w-full text-xs text-left">
                                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                            @foreach($call['countries'] as $countryRow)
                                                <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5">
                                                    <td class="px-3 py-1.5 font-medium text-gray-800 dark:text-gray-200">{{ $countryRow['country'] }}</td>
                                                    <td class="px-3 py-1.5 text-right font-bold text-gray-900 dark:text-white tabular-nums">{{ $countryRow['count'] }}</td>
                                                    <td class="px-3 py-1.5 text-right text-gray-500 dark:text-gray-400 tabular-nums w-20">{{ number_format($countryRow['percentage'], 1, ',', '') }}%</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 border border-dashed rounded-xl border-gray-200 dark:border-white/10 text-center">
                            <p class="text-xs text-gray-400 dark:text-gray-500">Aucune candidature soumise pour ce périmètre.</p>
                        </div>
                    @endif
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
