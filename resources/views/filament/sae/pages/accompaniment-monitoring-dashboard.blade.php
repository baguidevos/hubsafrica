<x-filament-panels::page>
    @php
        $kpis = $this->kpis;
        $matrix = $this->startupsMatrix;
        $progDeliverables = $this->programmeDeliverables;
        $phases = $this->phasesProgress;
        $alerts = $this->alerts;
        $selectedCohort = $this->selectedCohort;
    @endphp

    <div class="space-y-6">
        <!-- Bannière d'en-tête & Filtre Cohorte -->
        <div class="relative overflow-hidden rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-6 shadow-sm">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Dispositif M&E — Méthodologie Afric'innov / SAE-Acte
                    </div>
                    <h2 class="text-xl lg:text-2xl font-bold tracking-tight text-stone-900 dark:text-white">
                        Tableau de Bord de Suivi & d'Accompagnement
                    </h2>
                    <p class="text-xs lg:text-sm text-stone-500 dark:text-stone-400 max-w-2xl">
                        Supervision opérationnelle des 6 phases d'incubation : émargements aux formations, séances de coaching individuel (C1 & C2), livrables critiques et alertes de décrochage (< 75%).
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="cohortSelect" class="text-xs font-semibold text-stone-600 dark:text-stone-300">Cohorte :</label>
                        <select
                            id="cohortSelect"
                            wire:model.live="selectedCohortId"
                            class="text-xs rounded-xl border-stone-300 dark:border-stone-700 dark:bg-stone-800 text-stone-900 dark:text-white font-medium py-2 px-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            @forelse($this->cohorts as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->program?->name ?? 'Général' }})</option>
                            @empty
                                <option value="">Aucune cohorte configurée</option>
                            @endforelse
                        </select>
                    </div>

                    <button
                        wire:click="exportCsv"
                        type="button"
                        class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 text-stone-700 dark:text-stone-200 hover:bg-stone-50 dark:hover:bg-stone-700 shadow-sm transition"
                    >
                        <x-heroicon-o-arrow-down-tray class="w-4 h-4 text-emerald-600" />
                        <span>Exporter Synthèse (CSV)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 6 Cartes KPI Majeures (Directement inspirées des onglets Synthèse & Récap Mensuelle) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <!-- 1. Avancement Programme (SAEI) -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
                        <span>Avancement SAEI</span>
                        <x-heroicon-m-building-office class="w-4 h-4 text-indigo-500" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-stone-900 dark:text-white">{{ $kpis['avancement_programme'] }}%</span>
                        <span class="text-[11px] text-stone-400">({{ $kpis['livrables_programme_valides'] }}/{{ $kpis['livrables_programme_total'] }})</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-stone-100 dark:bg-stone-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-indigo-600 h-full rounded-full transition-all duration-500" style="width: {{ $kpis['avancement_programme'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-stone-400 mt-1 block">42 livrables de pilotage</span>
                </div>
            </div>

            <!-- 2. Avancement Startups -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
                        <span>Livrables Startups</span>
                        <x-heroicon-m-rocket-launch class="w-4 h-4 text-emerald-500" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-stone-900 dark:text-white">{{ $kpis['avancement_startups'] }}%</span>
                        <span class="text-[11px] text-stone-400">({{ $kpis['livrables_startups_valides'] }}/{{ $kpis['livrables_startups_total'] }})</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-stone-100 dark:bg-stone-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ $kpis['avancement_startups'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-stone-400 mt-1 block">20 livrables / entreprise</span>
                </div>
            </div>

            <!-- 3. Taux Présence Formations (F1 & F2) -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
                        <span>Présence Formations</span>
                        <x-heroicon-m-academic-cap class="w-4 h-4 text-blue-500" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-stone-900 dark:text-white">{{ $kpis['taux_presence_formation'] }}%</span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-stone-100 dark:bg-stone-800 h-2 rounded-full overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $kpis['taux_presence_formation'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-stone-400 mt-1 block">6 créneaux J1 à J3</span>
                </div>
            </div>

            <!-- 4. Taux Présence Coaching (C1 & C2) -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
                        <span>Présence Coaching</span>
                        <x-heroicon-m-user-group class="w-4 h-4 text-amber-500" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black {{ $kpis['taux_presence_coaching'] < 75 ? 'text-rose-600' : 'text-stone-900 dark:text-white' }}">
                            {{ $kpis['taux_presence_coaching'] }}%
                        </span>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-stone-100 dark:bg-stone-800 h-2 rounded-full overflow-hidden">
                        <div class="{{ $kpis['taux_presence_coaching'] < 75 ? 'bg-rose-500' : 'bg-amber-500' }} h-full rounded-full transition-all duration-500" style="width: {{ $kpis['taux_presence_coaching'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-stone-400 mt-1 block">Seuil alerte : < 75%</span>
                </div>
            </div>

            <!-- 5. Heures Coaching Délivrées -->
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs text-stone-500 dark:text-stone-400 font-medium">
                        <span>Heures Coaching</span>
                        <x-heroicon-m-clock class="w-4 h-4 text-purple-500" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-stone-900 dark:text-white">{{ $kpis['heures_coaching_total'] }}</span>
                        <span class="text-xs font-semibold text-stone-400">heures</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-[10px] text-stone-500 dark:text-stone-400 font-medium">Volume effectif réalisé</span>
                    <span class="text-[10px] text-stone-400 block mt-0.5">8h (C1) + 8h (C2) visées</span>
                </div>
            </div>

            <!-- 6. Alertes & Risque d'abandon -->
            <div class="rounded-2xl border {{ $kpis['alertes_presence_count'] > 0 ? 'border-rose-300 dark:border-rose-800 bg-rose-50/50 dark:bg-rose-950/20' : 'border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900' }} p-4 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between text-xs {{ $kpis['alertes_presence_count'] > 0 ? 'text-rose-700 dark:text-rose-400 font-bold' : 'text-stone-500 dark:text-stone-400 font-medium' }}">
                        <span>Alertes M&E</span>
                        <x-heroicon-m-exclamation-triangle class="w-4 h-4 {{ $kpis['alertes_presence_count'] > 0 ? 'text-rose-600 animate-bounce' : 'text-stone-400' }}" />
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black {{ $kpis['alertes_presence_count'] > 0 ? 'text-rose-600' : 'text-stone-900 dark:text-white' }}">
                            {{ $kpis['alertes_presence_count'] }}
                        </span>
                        <span class="text-xs text-stone-500">startup(s) < 75%</span>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="text-[10px] {{ $kpis['soumissions_en_retard'] > 0 ? 'text-amber-600 font-semibold' : 'text-stone-400' }}">
                        {{ $kpis['soumissions_en_retard'] }} dépôt(s) en retard
                    </span>
                </div>
            </div>
        </div>

        <!-- Onglets de Navigation Intégrés -->
        <div class="border-b border-stone-200 dark:border-stone-800 flex items-center gap-6 text-sm font-semibold">
            <button
                type="button"
                wire:click="$set('activeTab', 'startups')"
                class="pb-3 border-b-2 flex items-center gap-2 transition {{ $activeTab === 'startups' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}"
            >
                <x-heroicon-o-table-cells class="w-4 h-4" />
                <span>Matrice de Suivi Startups ({{ count($matrix) }})</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'programme')"
                class="pb-3 border-b-2 flex items-center gap-2 transition {{ $activeTab === 'programme' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}"
            >
                <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
                <span>Livrables SAEI & Programme (42)</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'phases')"
                class="pb-3 border-b-2 flex items-center gap-2 transition {{ $activeTab === 'phases' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}"
            >
                <x-heroicon-o-arrow-trending-up class="w-4 h-4" />
                <span>Jalons des 6 Phases</span>
            </button>

            <button
                type="button"
                wire:click="$set('activeTab', 'alerts')"
                class="pb-3 border-b-2 flex items-center gap-2 transition {{ $activeTab === 'alerts' ? 'border-emerald-600 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-stone-500 hover:text-stone-800 dark:hover:text-stone-300' }}"
            >
                <x-heroicon-o-bell-alert class="w-4 h-4" />
                <span>Alertes de Décrochage ({{ count($alerts) }})</span>
                @if(count($alerts) > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-rose-500 text-white font-bold">{{ count($alerts) }}</span>
                @endif
            </button>
        </div>

        <!-- CONTENU DU TAB 1 : MATRICE STARTUPS -->
        @if($activeTab === 'startups')
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white uppercase tracking-wider">
                            Matrice de Suivi Individuel — Startups de la Cohorte
                        </h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                            Reconstitution directe du tableau « Suivi Startups » du modèle Excel Afric'innov.
                        </p>
                    </div>
                    <span class="text-xs font-semibold text-stone-400">{{ count($matrix) }} startup(s) active(s)</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-50 dark:bg-stone-800/60 text-stone-500 dark:text-stone-400 font-bold border-b border-stone-200 dark:border-stone-800 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Startup & Secteur</th>
                                <th class="px-4 py-3">Fondateur</th>
                                <th class="px-4 py-3">Coach Référent</th>
                                <th class="px-4 py-3 text-center">Séances C1 (PSF)</th>
                                <th class="px-4 py-3 text-center">Séances C2 (PMF)</th>
                                <th class="px-4 py-3 text-center">Total Séances</th>
                                <th class="px-4 py-3 text-right">Heures Coaching</th>
                                <th class="px-4 py-3">Livrables Validés</th>
                                <th class="px-4 py-3 text-center">Statut M&E</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                            @forelse($matrix as $row)
                                <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/40 transition">
                                    <td class="px-4 py-3 font-semibold text-stone-900 dark:text-white">
                                        <div>{{ $row['project_name'] }}</div>
                                        <div class="text-[10px] font-normal text-stone-400">{{ $row['sector'] }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-stone-600 dark:text-stone-300">
                                        <div>{{ $row['entrepreneur_name'] }}</div>
                                        @if($row['entrepreneur_phone'])
                                            <div class="text-[10px] text-stone-400">{{ $row['entrepreneur_phone'] }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300 font-medium">
                                            <x-heroicon-m-user class="w-3 h-3 text-stone-400" />
                                            {{ $row['coach_name'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-stone-700 dark:text-stone-300">
                                        <span class="px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300">
                                            {{ $row['c1_done'] }} / 8
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-stone-700 dark:text-stone-300">
                                        <span class="px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300">
                                            {{ $row['c2_done'] }} / 8
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-stone-900 dark:text-white">
                                        {{ $row['coaching_sessions_done'] }} / 16
                                    </td>
                                    <td class="px-4 py-3 text-right font-black text-stone-900 dark:text-white">
                                        {{ $row['coaching_hours'] }} h
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 bg-stone-100 dark:bg-stone-800 h-2 rounded-full overflow-hidden">
                                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $row['livrables_taux'] }}%"></div>
                                            </div>
                                            <span class="font-bold text-stone-700 dark:text-stone-300">{{ $row['livrables_valides'] }}/{{ $row['livrables_total'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($row['status'] === 'ALERTE_PRESENCE')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300">
                                                <x-heroicon-m-exclamation-triangle class="w-3 h-3 text-rose-600" />
                                                Risque abandon (< 75%)
                                            </span>
                                        @elseif($row['status'] === 'RETARD')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300">
                                                <x-heroicon-m-clock class="w-3 h-3 text-amber-600" />
                                                Retard Livrable
                                            </span>
                                        @elseif($row['status'] === 'VIGILANCE')
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-semibold bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300">
                                                Vigilance ({{ $row['coaching_presence_rate'] }}%)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                                <x-heroicon-m-check-circle class="w-3 h-3 text-emerald-600" />
                                                En bonne voie
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a
                                            href="{{ route('filament.sae.resources.projects.view', ['tenant' => filament()->getTenant(), 'record' => $row['project_id']]) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 transition font-medium"
                                        >
                                            <span>Détails</span>
                                            <x-heroicon-m-chevron-right class="w-3 h-3" />
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-8 text-center text-stone-400">
                                        Aucune startup enregistrée dans cette cohorte.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- CONTENU DU TAB 2 : LIVRABLES PROGRAMME / SAEI (42 LIVRABLES) -->
        @if($activeTab === 'programme')
            <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-200 dark:border-stone-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-stone-900 dark:text-white uppercase tracking-wider">
                            Suivi des Livrables Programme (42 Livrables SAEI)
                        </h3>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">
                            Matrice de contrôle des livrables institutionnels : conventions, diagnostics initiaux, émargements, synthèses mi-parcours et bilans finaux.
                        </p>
                    </div>
                    <a
                        href="{{ route('filament.sae.resources.deliverable-definitions.create', ['tenant' => filament()->getTenant()]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition"
                    >
                        <x-heroicon-m-plus class="w-4 h-4" />
                        <span>Nouveau Livrable</span>
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-stone-50 dark:bg-stone-800/60 text-stone-500 dark:text-stone-400 font-bold border-b border-stone-200 dark:border-stone-800 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 w-12 text-center">N°</th>
                                <th class="px-4 py-3">Livrable Institutionnel</th>
                                <th class="px-4 py-3">Jalon Pédagogique</th>
                                <th class="px-4 py-3">Responsable</th>
                                <th class="px-4 py-3">Échéance</th>
                                <th class="px-4 py-3 text-center">Statut Dépôt</th>
                                <th class="px-4 py-3 text-right">Fichier / Dépôt</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                            @forelse($progDeliverables as $item)
                                <tr class="hover:bg-stone-50/50 dark:hover:bg-stone-800/40 transition">
                                    <td class="px-4 py-3 text-center font-bold text-stone-400">
                                        {{ $item['order'] }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-stone-900 dark:text-white">
                                        {{ $item['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-stone-500 dark:text-stone-400">
                                        {{ $item['phase'] }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                                            {{ $item['role'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-stone-500 dark:text-stone-400">
                                        {{ $item['deadline'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item['status'] === 'VALIDE' || $item['status'] === 'VALIDEE')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                                <x-heroicon-m-check class="w-3 h-3 text-emerald-600" />
                                                Validé
                                            </span>
                                        @elseif($item['status'])
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 dark:bg-blue-950/80 dark:text-blue-300">
                                                Déposé ({{ $item['status'] }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-stone-100 text-stone-500 dark:bg-stone-800 dark:text-stone-400">
                                                En attente
                                            </span>
                                        @endif

                                        @if($item['is_late'])
                                            <span class="ml-1 text-[9px] font-black text-rose-600 uppercase">En retard</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        @if($item['file_path'])
                                            <a
                                                href="{{ asset('storage/' . $item['file_path']) }}"
                                                target="_blank"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 hover:bg-emerald-100 font-semibold"
                                            >
                                                <x-heroicon-m-arrow-down-tray class="w-3 h-3" />
                                                <span>Télécharger</span>
                                            </a>
                                        @else
                                            <a
                                                href="{{ route('filament.sae.resources.deliverable-submissions.create', ['tenant' => filament()->getTenant()]) }}"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 font-medium"
                                            >
                                                <x-heroicon-m-arrow-up-tray class="w-3 h-3" />
                                                <span>Déposer</span>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-stone-400">
                                        Aucun livrable programme configuré. Définissez les livrables institutionnels dans le menu Livrables.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- CONTENU DU TAB 3 : JALONS DES 6 PHASES (STARTUP LOOP) -->
        @if($activeTab === 'phases')
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($phases as $phase)
                    <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-5 shadow-sm flex flex-col justify-between hover:border-emerald-500/50 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[11px] font-black uppercase tracking-wider bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-300">
                                    {{ $phase['weeks'] }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $phase['status'] === 'EN_COURS' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' : 'bg-stone-100 text-stone-500 dark:bg-stone-800 dark:text-stone-400' }}">
                                    {{ $phase['status'] === 'EN_COURS' ? 'Phase Active' : ($phase['status'] === 'TERMINEE' ? 'Complétée' : 'À Venir') }}
                                </span>
                            </div>

                            <h4 class="text-base font-bold text-stone-900 dark:text-white mt-3">
                                {{ $phase['name'] }}
                            </h4>

                            <p class="text-xs text-stone-500 dark:text-stone-400 mt-2 leading-relaxed">
                                {{ $phase['description'] }}
                            </p>
                        </div>

                        <div class="mt-5 pt-4 border-t border-stone-100 dark:border-stone-800 flex items-center justify-between text-xs font-medium text-stone-500 dark:text-stone-400">
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-m-calendar class="w-4 h-4 text-stone-400" />
                                <span>{{ $phase['starts_on'] }} — {{ $phase['ends_on'] }}</span>
                            </div>
                            <div class="flex items-center gap-1 text-emerald-600 font-bold">
                                <span>{{ $phase['deliverables_count'] }} livrables</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- CONTENU DU TAB 4 : ALERTES DE DÉCROCHAGE & REMÉDIATION -->
        @if($activeTab === 'alerts')
            <div class="space-y-4">
                @forelse($alerts as $alert)
                    <div class="rounded-2xl border {{ $alert['type'] === 'danger' ? 'border-rose-200 dark:border-rose-800 bg-rose-50/40 dark:bg-rose-950/20' : 'border-amber-200 dark:border-amber-800 bg-amber-50/40 dark:bg-amber-950/20' }} p-5 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="p-2.5 rounded-xl {{ $alert['type'] === 'danger' ? 'bg-rose-100 text-rose-600 dark:bg-rose-900/60 dark:text-rose-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/60 dark:text-amber-400' }}">
                                <x-heroicon-m-exclamation-triangle class="w-6 h-6" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-stone-900 dark:text-white">
                                    {{ $alert['title'] }}
                                </h4>
                                <p class="text-xs text-stone-600 dark:text-stone-400 mt-1">
                                    {{ $alert['message'] }}
                                </p>
                                @if($alert['phone'])
                                    <div class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-stone-700 dark:text-stone-300">
                                        <x-heroicon-m-phone class="w-3.5 h-3.5 text-emerald-600" />
                                        <span>Contact : {{ $alert['phone'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('filament.sae.resources.projects.view', ['tenant' => filament()->getTenant(), 'record' => $alert['project_id']]) }}"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-white dark:bg-stone-800 border border-stone-200 dark:border-stone-700 text-stone-700 dark:text-stone-200 hover:bg-stone-50 shadow-sm transition"
                            >
                                Voir dossier startup
                            </a>
                            <a
                                href="{{ route('filament.sae.resources.coaching-sessions.create', ['tenant' => filament()->getTenant()]) }}"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition"
                            >
                                Planifier remédiation
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-12 text-center">
                        <div class="inline-flex p-3 rounded-full bg-emerald-50 dark:bg-emerald-950 text-emerald-600 mb-3">
                            <x-heroicon-o-check-badge class="w-8 h-8" />
                        </div>
                        <h4 class="text-base font-bold text-stone-900 dark:text-white">Aucune alerte active</h4>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-1 max-w-md mx-auto">
                            Toutes les startups de la cohorte présentent une assiduité supérieure à 75% et leurs livrables sont soumis conformément au chronogramme.
                        </p>
                    </div>
                @endforelse
            </div>
        @endif
    </div>
</x-filament-panels::page>
