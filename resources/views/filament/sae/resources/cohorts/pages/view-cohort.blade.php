@php
    /** @var \App\Models\Cohort $record */
    $record = $this->getRecord();
    $statusLabel = method_exists($record->status, 'getLabel') ? $record->status->getLabel() : (string) $record->status;
    $statusColor = method_exists($record->status, 'getColor') ? $record->status->getColor() : 'gray';
    $isOpen = $record->status === \App\Enums\CohortStatus::OUVERTE;

    $enrollmentsCount = $record->enrollments_count ?? 0;
    $applicationsCount = $record->applications_count ?? 0;
    $pendingCount = $record->pending_applications_count ?? 0;
    $acceptedCount = $record->accepted_applications_count ?? 0;
    $occupancyLabel = \App\Services\ProgramEvaluationPresenter::formatOccupancyRate($enrollmentsCount, $record->capacity);
    $occupancyPercent = ($record->capacity && $record->capacity > 0)
        ? min(100, (int) round(($enrollmentsCount / $record->capacity) * 100))
        : null;
@endphp

<div class="space-y-6">
    {{-- 1. Bannière de pilotage de la cohorte --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-[#1e225a] p-6 sm:p-8 text-white shadow-lg border border-slate-800">
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-[#F8B81D]/15 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-2.5 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <x-filament::badge :color="$statusColor">
                        {{ $statusLabel }}
                    </x-filament::badge>

                    @if($record->program)
                        <x-filament::badge color="info" icon="heroicon-m-academic-cap">
                            {{ $record->program->name }}
                        </x-filament::badge>
                    @endif

                    @if($record->program?->level)
                        <x-filament::badge color="primary">
                            {{ $record->program->level->name }}
                        </x-filament::badge>
                    @endif

                    @if($isOpen)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Inscriptions ouvertes
                        </span>
                    @endif
                </div>

                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-white">
                    {{ $record->name }}
                </h2>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300">
                    <span class="inline-flex items-center gap-1.5">
                        <x-heroicon-m-document-check class="w-4 h-4 text-slate-400" />
                        <span>Formulaire : <strong class="text-white">{{ $record->applicationForm?->name ?? 'Modèle standard Hubs Africa' }}</strong></span>
                    </span>
                    @if($record->number)
                        <span class="text-slate-600">&bull;</span>
                        <span>Session n° <strong class="text-[#F8B81D]">{{ $record->number }}</strong></span>
                    @endif
                </div>
            </div>

            @if($record->program)
                <div class="shrink-0">
                    <a
                        href="{{ \App\Filament\Sae\Resources\Programs\ProgramResource::getUrl('view', ['record' => $record->program]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 text-xs font-bold transition"
                    >
                        <x-heroicon-o-academic-cap class="w-4 h-4 text-[#F8B81D]" />
                        <span>Ouvrir le programme parent</span>
                    </a>
                </div>
            @endif
        </div>

        {{-- 4 KPIs de pilotage --}}
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Occupation / Admises</span>
                    <x-heroicon-o-users class="w-4 h-4 text-[#F8B81D]" />
                </div>
                <div class="mt-1.5 text-xl font-black text-white">{{ $occupancyLabel }}</div>
                @if($occupancyPercent !== null)
                    <div class="mt-2 h-1.5 w-full rounded-full bg-white/10 overflow-hidden">
                        <div class="h-full rounded-full bg-[#F8B81D]" style="width: {{ $occupancyPercent }}%"></div>
                    </div>
                @else
                    <div class="mt-1 text-[11px] text-slate-300">Capacité non plafonnée</div>
                @endif
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Candidatures reçues</span>
                    <x-heroicon-o-inbox-stack class="w-4 h-4 text-sky-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $applicationsCount }} dossier(s)</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    <span class="text-emerald-300 font-semibold">{{ $acceptedCount }} retenue(s)</span>
                    <span class="text-slate-500 mx-1">&bull;</span>
                    <span class="text-amber-300 font-semibold">{{ $pendingCount }} en attente</span>
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Parcours &amp; Jury</span>
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-emerald-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $record->phases_count ?? 0 }} phase(s)</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    {{ $record->evaluators_count ?? 0 }} évaluateur(s) assigné(s)
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Sessions de coaching</span>
                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-purple-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $record->coaching_sessions_count ?? 0 }}</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    Séance(s) enregistrée(s)
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Calendrier de la Cohorte --}}
    <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs">
        <div class="flex items-center gap-2 mb-4">
            <x-heroicon-o-calendar-days class="w-5 h-5 text-primary-600 dark:text-primary-400" />
            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                Calendrier de la Promotion
            </h3>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date de lancement</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->start_date ? $record->start_date->format('d/m/Y') : 'Non définie' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Date de clôture</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    {{ $record->end_date ? $record->end_date->format('d/m/Y') : 'Non définie' }}
                </p>
            </div>
            <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-4">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Durée du cycle</span>
                <p class="mt-1 text-sm font-bold text-gray-900 dark:text-white">
                    @if($record->start_date && $record->end_date)
                        {{ $record->start_date->diffInWeeks($record->end_date) }} semaine(s)
                    @else
                        À définir
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- 3. Contenu éditorial & Diffusion --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div class="lg:col-span-8">
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Objectifs &amp; Thématiques de la promotion</h3>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $record->objectives ?: 'Aucun objectif spécifique renseigné pour cette cohorte.' }}
                </div>
            </div>
        </div>

        <div class="lg:col-span-4 space-y-6">
            {{-- Lien direct de candidature --}}
            <div
                x-data="{ copied: false }"
                class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3"
            >
                <div class="flex items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Lien direct de candidature</h3>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $isOpen ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                        {{ $isOpen ? 'Ouvert' : $statusLabel }}
                    </span>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Diffusez cette URL auprès des entrepreneurs pour un dépôt direct sur cette promotion.
                </p>

                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $record->public_join_url }}"
                        class="w-full rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 font-mono truncate"
                    >
                    <button
                        type="button"
                        @click="navigator.clipboard.writeText('{{ $record->public_join_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="shrink-0 px-3 py-2 rounded-lg bg-primary-600 hover:bg-primary-500 text-white text-xs font-bold transition"
                    >
                        <span x-show="!copied">Copier</span>
                        <span x-show="copied" x-cloak>Copié !</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
