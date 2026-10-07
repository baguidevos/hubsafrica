<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Barre d'outils du calendrier global -->
        <x-filament::section compact>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <x-filament::icon-button
                        wire:click="previousMonth"
                        icon="heroicon-m-chevron-left"
                        label="Mois précédent"
                        color="gray"
                        size="sm"
                    />
                    <x-filament::button
                        wire:click="today"
                        color="gray"
                        size="xs"
                    >
                        Aujourd'hui
                    </x-filament::button>
                    <x-filament::icon-button
                        wire:click="nextMonth"
                        icon="heroicon-m-chevron-right"
                        label="Mois suivant"
                        color="gray"
                        size="sm"
                    />
                    <h2 class="ml-2 text-lg font-bold text-gray-950 dark:text-white capitalize">
                        {{ $this->monthName }}
                    </h2>
                </div>

                <!-- Filtres -->
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Type d'événement -->
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Vue :</label>
                        <select
                            wire:model.live="selectedEventType"
                            class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">🌟 Tous les événements</option>
                            <option value="sessions">👤 Séances de coaching</option>
                            <option value="phases">🚩 Jalons &amp; Phases</option>
                            <option value="cohorts">🚀 Promotions &amp; Cohortes</option>
                        </select>
                    </div>

                    <!-- Filtre Cohorte -->
                    <div class="flex items-center gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Cohorte :</label>
                        <select
                            wire:model.live="selectedCohortId"
                            class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                        >
                            <option value="all">Toutes les cohortes</option>
                            @foreach($this->cohorts as $coh)
                                <option value="{{ $coh->id }}">{{ $coh->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filtre Coach -->
                    @if($selectedEventType === 'all' || $selectedEventType === 'sessions')
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Coach :</label>
                            <select
                                wire:model.live="selectedCoachId"
                                class="text-xs rounded-xl border-gray-300 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 text-gray-900 dark:text-white py-1.5 px-3 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="all">Tous les coachs</option>
                                @foreach($this->coaches as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Légende visuelle -->
            <div class="flex flex-wrap items-center gap-3 pt-3 mt-3 border-t border-gray-100 dark:border-white/10 text-[11px] text-gray-600 dark:text-gray-400">
                <span class="font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[10px]">Légende :</span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span> Séance individuelle
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Atelier collectif / Boot camp
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> 🚩 Début de Jalon
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> 🏁 Échéance de Jalon
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> 🚀 Lancement Promotion
                </span>
                <span class="inline-flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> 🎓 Demo Day / Clôture
                </span>
            </div>
        </x-filament::section>

        <!-- Grille du calendrier -->
        <x-filament::section>
            <div class="-mx-6 -my-6 overflow-hidden">
                <!-- En-tête des jours de la semaine -->
                <div class="grid grid-cols-7 border-b border-gray-200/70 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 text-center text-xs font-bold text-gray-600 dark:text-gray-400 py-2.5 uppercase tracking-wider">
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mer</div>
                    <div>Jeu</div>
                    <div>Ven</div>
                    <div>Sam</div>
                    <div>Dim</div>
                </div>

                <!-- Jours du mois -->
                <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-gray-200/70 dark:divide-white/10">
                    @foreach($this->days as $d)
                        <div class="min-h-[125px] p-2 flex flex-col justify-between transition {{ $d['isCurrentMonth'] ? 'bg-transparent' : 'bg-gray-50/50 dark:bg-white/[0.02] text-gray-400' }} {{ $d['isToday'] ? 'ring-2 ring-inset ring-primary-500 bg-primary-50/20 dark:bg-primary-950/20' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ $d['isToday'] ? 'bg-primary-600 text-white' : ($d['isCurrentMonth'] ? 'text-gray-900 dark:text-white' : 'text-gray-400') }}">
                                    {{ $d['dayNumber'] }}
                                </span>
                                @if($d['events']->count() > 0)
                                    <span class="text-[10px] font-bold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/50 px-1.5 py-0.5 rounded-full border border-primary-200/50 dark:border-primary-800/50">
                                        {{ $d['events']->count() }}
                                    </span>
                                @endif
                            </div>

                            <!-- Liste des événements du jour (Coaching + Jalons + Cohortes) -->
                            <div class="space-y-1 overflow-y-auto max-h-[105px]">
                                @foreach($d['events'] as $e)
                                    @php
                                        $colorClass = match($e['color']) {
                                            'indigo' => 'bg-indigo-50 border-indigo-200 text-indigo-900 dark:bg-indigo-950/40 dark:border-indigo-800 dark:text-indigo-200',
                                            'purple' => 'bg-purple-50 border-purple-200 text-purple-900 dark:bg-purple-950/40 dark:border-purple-800 dark:text-purple-200',
                                            'emerald' => 'bg-emerald-50 border-emerald-200 text-emerald-900 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-200',
                                            'blue' => 'bg-blue-50 border-blue-200 text-blue-900 dark:bg-blue-950/40 dark:border-blue-800 dark:text-blue-200',
                                            'amber' => 'bg-amber-50 border-amber-200 text-amber-900 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-200',
                                            'rose' => 'bg-rose-50 border-rose-200 text-rose-900 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-200',
                                            default => 'bg-teal-50 border-teal-200 text-teal-900 dark:bg-teal-950/40 dark:border-teal-800 dark:text-teal-200',
                                        };

                                        $actionMethod = match($e['kind']) {
                                            'phase' => 'openPhase(' . $e['id'] . ')',
                                            'cohort' => 'openCohort(' . $e['id'] . ')',
                                            default => 'openSession(' . $e['id'] . ')',
                                        };
                                    @endphp
                                    <button
                                        wire:click="{{ $actionMethod }}"
                                        type="button"
                                        class="w-full text-left p-1 rounded-lg border text-[10.5px] leading-tight truncate block transition hover:opacity-85 shadow-xs {{ $colorClass }}"
                                        title="{{ $e['title'] }} ({{ $e['subtitle'] }})"
                                    >
                                        <div class="flex items-center gap-1 font-semibold truncate">
                                            <span class="tabular-nums font-mono text-[9.5px] shrink-0 opacity-80">[{{ $e['time'] }}]</span>
                                            <span class="truncate">{{ $e['title'] }}</span>
                                        </div>
                                        @if($e['subtitle'])
                                            <div class="text-[9px] opacity-75 truncate">
                                                {{ $e['subtitle'] }}
                                            </div>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </x-filament::section>

        <!-- 1. Modal de détail de séance de coaching -->
        @if($this->selectedSession)
            @php
                $sel = $this->selectedSession;
                $dateObj = \Carbon\Carbon::parse($sel->date);
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs" wire:keydown.escape="closeModal">
                <div class="max-w-xl w-full">
                    <x-filament::section class="shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <x-slot name="heading">
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <div class="mb-1">
                                        <x-filament::badge color="primary">
                                            {{ is_object($sel->type) && method_exists($sel->type, 'getLabel') ? $sel->type->getLabel() : $sel->type }} &bull; {{ is_object($sel->status) && method_exists($sel->status, 'getLabel') ? $sel->status->getLabel() : $sel->status }}
                                        </x-filament::badge>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-950 dark:text-white">
                                        {{ $sel->theme ?: 'Séance de coaching' }}
                                    </h3>
                                </div>
                                <x-filament::icon-button
                                    wire:click="closeModal"
                                    icon="heroicon-m-x-mark"
                                    color="gray"
                                    label="Fermer"
                                />
                            </div>
                        </x-slot>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Date &amp; Horaires</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $dateObj->translatedFormat('d F Y à H:i') }}</span>
                                <span class="text-gray-500"> ({{ $sel->duration }} min)</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Coach intervenant</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $sel->coach?->name ?: '—' }}</span>
                                <span class="block text-[10px] text-gray-400">{{ $sel->coach?->email }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Projet</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $sel->project?->name ?: 'Non renseigné' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Cohorte</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $sel->cohort?->name ?: 'Toutes' }}</span>
                            </div>
                        </div>

                        @if($sel->objectives)
                            <div class="text-xs space-y-1 mt-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white">Objectifs :</h4>
                                <p class="p-2.5 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $sel->objectives }}</p>
                            </div>
                        @endif

                        @if($sel->report)
                            <div class="text-xs space-y-1 mt-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white">Compte-rendu :</h4>
                                <p class="p-2.5 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $sel->report }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-gray-100 dark:border-white/10">
                            <x-filament::button
                                wire:click="closeModal"
                                color="gray"
                                size="sm"
                            >
                                Fermer
                            </x-filament::button>
                        </div>
                    </x-filament::section>
                </div>
            </div>
        @endif

        <!-- 2. Modal de détail de Jalon / Phase de cohorte -->
        @if($this->selectedPhase)
            @php
                $phase = $this->selectedPhase;
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs" wire:keydown.escape="closeModal">
                <div class="max-w-lg w-full">
                    <x-filament::section class="shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <x-slot name="heading">
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <div class="mb-1">
                                        <x-filament::badge color="info">
                                            Jalon pédagogique #{{ $phase->position }} &bull; {{ $phase->status?->getLabel() ?? $phase->status }}
                                        </x-filament::badge>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-950 dark:text-white flex items-center gap-1.5">
                                        <span>🚩</span>
                                        <span>{{ $phase->name }}</span>
                                    </h3>
                                </div>
                                <x-filament::icon-button
                                    wire:click="closeModal"
                                    icon="heroicon-m-x-mark"
                                    color="gray"
                                    label="Fermer"
                                />
                            </div>
                        </x-slot>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Promotion / Cohorte</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $phase->cohort?->name ?? '—' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Programme parent</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $phase->cohort?->program?->name ?? '—' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Date d'ouverture</span>
                                <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ $phase->starts_on ? $phase->starts_on->format('d/m/Y') : 'Non planifiée' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Date d'échéance / Clôture</span>
                                <span class="font-bold text-purple-600 dark:text-purple-400">{{ $phase->ends_on ? $phase->ends_on->format('d/m/Y') : 'Non planifiée' }}</span>
                            </div>
                        </div>

                        @if($phase->programPhase?->passing_criteria)
                            <div class="text-xs space-y-1 mt-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white">Critères de passage :</h4>
                                <p class="p-2.5 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $phase->programPhase->passing_criteria }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-gray-100 dark:border-white/10">
                            <x-filament::button
                                wire:click="closeModal"
                                color="gray"
                                size="sm"
                            >
                                Fermer
                            </x-filament::button>
                        </div>
                    </x-filament::section>
                </div>
            </div>
        @endif

        <!-- 3. Modal de détail de Cohorte / Promotion -->
        @if($this->selectedCohortDetail)
            @php
                $coh = $this->selectedCohortDetail;
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/60 backdrop-blur-xs" wire:keydown.escape="closeModal">
                <div class="max-w-lg w-full">
                    <x-filament::section class="shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                        <x-slot name="heading">
                            <div class="flex items-center justify-between w-full">
                                <div>
                                    <div class="mb-1">
                                        <x-filament::badge color="primary">
                                            Promotion active &bull; {{ $coh->status?->getLabel() ?? $coh->status }}
                                        </x-filament::badge>
                                    </div>
                                    <h3 class="text-base font-bold text-gray-950 dark:text-white flex items-center gap-1.5">
                                        <span>🚀</span>
                                        <span>{{ $coh->name }}</span>
                                    </h3>
                                </div>
                                <x-filament::icon-button
                                    wire:click="closeModal"
                                    icon="heroicon-m-x-mark"
                                    color="gray"
                                    label="Fermer"
                                />
                            </div>
                        </x-slot>

                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Programme</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $coh->program?->name ?? '—' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Capacité</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $coh->capacity ? $coh->capacity . ' startups' : 'Illimitée' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Lancement officiel</span>
                                <span class="font-bold text-blue-600 dark:text-blue-400">{{ $coh->start_date ? $coh->start_date->format('d/m/Y') : 'Non planifié' }}</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <span class="block text-[10px] text-gray-400 font-semibold uppercase">Clôture / Demo Day</span>
                                <span class="font-bold text-amber-600 dark:text-amber-400">{{ $coh->end_date ? $coh->end_date->format('d/m/Y') : 'Non planifiée' }}</span>
                            </div>
                        </div>

                        @if($coh->phases->count() > 0)
                            <div class="text-xs space-y-1.5 mt-3">
                                <h4 class="font-semibold text-gray-900 dark:text-white">Jalons associés :</h4>
                                <div class="space-y-1">
                                    @foreach($coh->phases as $p)
                                        <div class="flex items-center justify-between p-2 rounded-xl bg-gray-50/50 dark:bg-white/5 border border-gray-100 dark:border-white/5 text-[11px]">
                                            <span class="font-semibold text-gray-900 dark:text-white">#{{ $p->position }} {{ $p->name }}</span>
                                            <span class="text-gray-500 dark:text-gray-400 font-mono text-[10px]">
                                                {{ $p->starts_on?->format('d/m') ?? '—' }} &rarr; {{ $p->ends_on?->format('d/m/Y') ?? '—' }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-2 pt-4 mt-4 border-t border-gray-100 dark:border-white/10">
                            <x-filament::button
                                wire:click="closeModal"
                                color="gray"
                                size="sm"
                            >
                                Fermer
                            </x-filament::button>
                        </div>
                    </x-filament::section>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
