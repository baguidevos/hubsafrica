@php
    /** @var \App\Models\Program $record */
    $record = $this->getRecord();
    $isOpen = $record->isAcceptingApplications();
    $visualUrl = $record->image_url ?? $record->sae?->logo_url;
    $statusLabel = method_exists($record->status, 'getLabel') ? $record->status->getLabel() : (string) $record->status;
    $statusColor = method_exists($record->status, 'getColor') ? $record->status->getColor() : 'gray';
    $acceptedCount = $record->accepted_applications_count ?? 0;
    $pendingCount = $record->pending_applications_count ?? 0;
    $totalApplications = $record->applications_count ?? 0;
@endphp

<div class="space-y-6">
    {{-- 1. Bannière de pilotage du programme --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-[#1e225a] p-6 sm:p-8 text-white shadow-lg border border-slate-800">
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-[#F8B81D]/15 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start gap-4 sm:gap-5 min-w-0">
                @if($visualUrl)
                    <img
                        src="{{ $visualUrl }}"
                        alt="{{ $record->name }}"
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-white border border-white/15 shadow-md shrink-0"
                    >
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-[#F8B81D] shrink-0">
                        <x-heroicon-o-academic-cap class="w-8 h-8" />
                    </div>
                @endif

                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge :color="$statusColor">
                            {{ $statusLabel }}
                        </x-filament::badge>

                        <x-filament::badge :color="$record->is_active ? 'success' : 'gray'" :icon="$record->is_active ? 'heroicon-m-eye' : 'heroicon-m-eye-slash'">
                            {{ $record->is_active ? 'Visible sur le portail' : 'Masqué du portail' }}
                        </x-filament::badge>

                        <x-filament::badge color="primary">
                            {{ $record->level?->name ?? 'Niveau transversal' }}
                        </x-filament::badge>

                        @if($isOpen)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#F8B81D]/20 text-[#F8B81D] border border-[#F8B81D]/40">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#F8B81D] animate-pulse"></span>
                                Candidatures ouvertes
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-white">
                        {{ $record->name }}
                    </h2>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300">
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-m-user-group class="w-4 h-4 text-slate-400" />
                            <span>Cible : <strong class="text-white">{{ $record->target ?: 'Non spécifiée' }}</strong></span>
                        </span>
                        <span class="text-slate-600">&bull;</span>
                        <span class="inline-flex items-center gap-1.5">
                            <x-heroicon-m-building-office-2 class="w-4 h-4 text-slate-400" />
                            <span>Capacité : <strong class="text-[#F8B81D]">{{ $record->capacity ? $record->capacity.' startups' : 'Illimitée' }}</strong></span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Raccourcis portail public --}}
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                @if($record->is_active)
                    <a
                        href="{{ $record->public_detail_url }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 text-xs font-bold transition"
                    >
                        <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 text-[#F8B81D]" />
                        <span>Voir la fiche publique</span>
                    </a>
                @endif
            </div>
        </div>

        {{-- 4 KPIs de pilotage --}}
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Candidatures</span>
                    <x-heroicon-o-inbox-stack class="w-4 h-4 text-[#F8B81D]" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $totalApplications }} dossier(s)</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    <span class="text-emerald-300 font-semibold">{{ $acceptedCount }} retenue(s)</span>
                    <span class="text-slate-500 mx-1">&bull;</span>
                    <span class="text-amber-300 font-semibold">{{ $pendingCount }} en attente</span>
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Cohortes &amp; Sessions</span>
                    <x-heroicon-o-calendar-days class="w-4 h-4 text-sky-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $record->cohorts_count ?? 0 }} cohorte(s)</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    Promotion(s) rattachée(s)
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Ingénierie pédagogique</span>
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-emerald-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $record->phases_count ?? 0 }} <span class="text-xs font-semibold text-slate-300">phases</span></div>
                <div class="mt-1 text-[11px] text-slate-300">
                    {{ $record->deliverable_definitions_count ?? 0 }} livrable(s) &bull; {{ $record->evaluation_criteria_count ?? 0 }} critère(s)
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Seuils du Jury</span>
                    <x-heroicon-o-scale class="w-4 h-4 text-purple-400" />
                </div>
                <div class="mt-1.5 text-lg font-black text-white">
                    Admis &ge; {{ $record->getAdmissionThreshold() }}/100
                </div>
                <div class="mt-1 text-[11px] text-slate-300">
                    Liste d'attente &ge; {{ $record->getWaitlistThreshold() }}/100
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Frise Calendrier du Cycle --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-clock class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                Calendrier du Programme
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ouverture candidatures</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->registration_start ? $record->registration_start->format('d/m/Y') : 'Non définie' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clôture candidatures</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->registration_end ? $record->registration_end->format('d/m/Y') : 'Non définie' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Lancement du programme</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->start_date ? $record->start_date->format('d/m/Y') : 'Non défini' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Clôture prévisionnelle</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->end_date ? $record->end_date->format('d/m/Y') : 'Non définie' }}
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Contenu éditorial & Outils SAE --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Colonne gauche (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Présentation générale</h3>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $record->description ?: 'Aucune description renseignée pour ce programme.' }}
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-flag class="w-5 h-5 text-amber-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Objectifs &amp; Livrables visés</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->objectives ?: 'Non renseignés.' }}
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Critères d'éligibilité</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->eligibility ?: 'Non renseignés.' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonne droite (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Lien direct de candidature --}}
            <div
                x-data="{ copied: false }"
                class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3"
            >
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Diffusion &amp; Candidature</h3>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isOpen ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                        {{ $isOpen ? 'Formulaire actif' : 'Hors période' }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Partagez ce lien direct avec les porteurs de projets pour qu'ils déposent leur dossier en ligne.
                </p>

                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $record->public_apply_url }}"
                        class="w-full rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-mono truncate"
                    >
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ $record->public_apply_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="shrink-0 px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition"
                    >
                        <span x-show="!copied">Copier</span>
                        <span x-show="copied" x-cloak>Copié !</span>
                    </button>
                </div>
            </div>

            {{-- Partenaires --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Partenaires associés</h3>
                    <x-filament::badge color="info">
                        {{ $record->partners_count ?? 0 }}
                    </x-filament::badge>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Gérez les partenaires, financeurs et mentors institutionnels dans l'onglet dédié ci-dessous.
                </p>
            </div>

            {{-- Affiche du programme si présente --}}
            @if($record->image_url)
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 shadow-xs space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Affiche officielle</h3>
                        <a
                            href="{{ $record->image_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400"
                        >
                            Ouvrir &rarr;
                        </a>
                    </div>
                    <img
                        src="{{ $record->image_url }}"
                        alt="{{ $record->name }}"
                        class="w-full max-h-64 object-contain rounded-xl bg-slate-950 border border-gray-200 dark:border-white/10"
                    >
                </div>
            @endif
        </div>
    </div>
</div>
