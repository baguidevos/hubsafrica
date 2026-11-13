@php
    /** @var \App\Models\Project $record */
    $record = $this->getRecord();
    $currentPanel = \Filament\Facades\Filament::getCurrentPanel()?->getId() ?? 'sae';

    $statusValue = $record->status instanceof \App\Enums\ProjectStatus ? $record->status->value : (string) ($record->status ?? '');
    $statusLabel = $record->status instanceof \App\Enums\ProjectStatus ? $record->status->getLabel() : ($statusValue ?: 'Actif');
    $statusColor = match ($statusValue) {
        'ACTIF', 'DIPLOME' => 'success',
        'BLOQUE' => 'danger',
        'STAGNANT' => 'warning',
        default => 'gray',
    };

    $sessionsCount = $record->sessions_count ?? $record->sessions->count();
    $submissionsCount = $record->submissions_count ?? $record->submissions->count();
    $activeAssignments = $record->assignments->where('status', 'ACTIF');
    $coachesCount = $activeAssignments->count();
    $cohort = $record->latestEnrollment?->cohort;
    $logoUrl = $record->logo ? \Illuminate\Support\Facades\Storage::disk('public')->url($record->logo) : null;
    $sdgs = is_array($record->sdgs) ? array_filter($record->sdgs) : [];
@endphp

<div class="space-y-6">
    {{-- 1. Bannière de pilotage du projet --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-[#1e225a] p-6 sm:p-8 text-white shadow-lg border border-slate-800">
        <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-[#F8B81D]/15 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 h-64 w-64 rounded-full bg-emerald-500/10 blur-3xl" aria-hidden="true"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start gap-4 sm:gap-5 min-w-0">
                @if($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $record->name }}"
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-white border border-white/15 shadow-md shrink-0"
                    >
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 border border-white/15 flex items-center justify-center text-[#F8B81D] font-black text-2xl shrink-0">
                        {{ strtoupper(substr($record->name, 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0 space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge :color="$statusColor">
                            {{ $statusLabel }}
                        </x-filament::badge>

                        @if($record->sector)
                            <x-filament::badge color="primary" icon="heroicon-m-building-storefront">
                                {{ $record->sector }}
                            </x-filament::badge>
                        @endif

                        @if($record->currentLevel)
                            <x-filament::badge color="info" icon="heroicon-m-chart-bar">
                                {{ $record->currentLevel->name }}
                            </x-filament::badge>
                        @endif

                        @if($cohort)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#F8B81D]/20 text-[#F8B81D] border border-[#F8B81D]/40">
                                <x-heroicon-m-academic-cap class="w-3.5 h-3.5" />
                                {{ $cohort->name }}
                            </span>
                        @endif
                    </div>

                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-white">
                        {{ $record->name }}
                    </h2>

                    @if($record->pitch)
                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-3xl">
                            {{ $record->pitch }}
                        </p>
                    @endif
                </div>
            </div>

            @if($record->entrepreneur)
                <div class="shrink-0 rounded-xl bg-white/[0.06] border border-white/10 px-4 py-3 text-xs space-y-1">
                    <span class="block text-[10px] uppercase tracking-wider text-slate-400 font-semibold">Porteur du projet</span>
                    <span class="block font-bold text-white text-sm">{{ $record->entrepreneur->full_name }}</span>
                    @if($record->entrepreneur->email)
                        <span class="block text-slate-300">{{ $record->entrepreneur->email }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- 4 KPIs de synthèse --}}
        <div class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Territoire</span>
                    <x-heroicon-o-map-pin class="w-4 h-4 text-[#F8B81D]" />
                </div>
                <div class="mt-1.5 text-lg font-black text-white truncate">
                    {{ $record->territory ?: 'Non renseigné' }}
                </div>
                <div class="mt-1 text-[11px] text-slate-300 truncate">
                    Équipe : <strong class="text-white">{{ $record->team_size ? $record->team_size.' personne(s)' : 'Non précisée' }}</strong>
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Maturation / Palier</span>
                    <x-heroicon-o-arrow-trending-up class="w-4 h-4 text-sky-400" />
                </div>
                <div class="mt-1.5 text-lg font-black text-white truncate">
                    {{ $record->currentLevel?->name ?? 'Non défini' }}
                </div>
                <div class="mt-1 text-[11px] text-slate-300 truncate">
                    Entrée : {{ $record->initialLevel?->name ?? '—' }}
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Sessions de coaching</span>
                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-emerald-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $sessionsCount }}</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    {{ $coachesCount }} coach(es) actif(s)
                </div>
            </div>

            <div class="rounded-xl bg-white/[0.06] border border-white/10 p-4">
                <div class="flex items-center justify-between text-xs text-slate-300">
                    <span>Livrables soumis</span>
                    <x-heroicon-o-document-check class="w-4 h-4 text-purple-400" />
                </div>
                <div class="mt-1.5 text-2xl font-black text-white">{{ $submissionsCount }}</div>
                <div class="mt-1 text-[11px] text-slate-300">
                    Dossier(s) méthodologique(s)
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Corps de la fiche projet (8 / 4 colonnes) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Colonne principale (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            {{-- Présentation générale --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Présentation du Projet</h3>
                </div>
                <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $record->description ?: 'Aucune description détaillée renseignée pour le moment.' }}
                </div>
            </div>

            {{-- Problème & Solution --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Problème adressé</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->problem ?: 'Non renseigné.' }}
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-sparkles class="w-5 h-5 text-emerald-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Solution proposée</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->solution ?: 'Non renseignée.' }}
                    </div>
                </div>
            </div>

            {{-- Produit/Service & Proposition de valeur --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-cube class="w-5 h-5 text-sky-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Produit / Service</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->product_service ?: 'Non renseigné.' }}
                    </div>
                </div>

                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-star class="w-5 h-5 text-purple-500" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Proposition de valeur</h3>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                        {{ $record->value_proposition ?: 'Non renseignée.' }}
                    </div>
                </div>
            </div>

            {{-- Cible & Marché --}}
            @if($record->target || $record->market || ! empty($sdgs))
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-globe-alt class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Marché, Cible &amp; Impact (ODD)</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Clientèle cible</span>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $record->target ?: 'Non renseignée.' }}</p>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Marché visé</span>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $record->market ?: 'Non renseigné.' }}</p>
                        </div>
                    </div>

                    @if(! empty($sdgs))
                        <div class="pt-3 border-t border-gray-100 dark:border-white/10">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Objectifs de Développement Durable (ODD)</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sdgs as $sdg)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/50">
                                        {{ $sdg }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Colonne latérale (4 cols) --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Parcours & Paliers --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-4">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-flag class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Parcours d'Incubation</h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-white/10">
                        <span class="text-gray-500 dark:text-gray-400">Palier d'entrée</span>
                        <span class="font-bold text-gray-900 dark:text-white">{{ $record->initialLevel?->name ?? 'Non défini' }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-white/10">
                        <span class="text-gray-500 dark:text-gray-400">Palier actuel</span>
                        <x-filament::badge color="info">{{ $record->currentLevel?->name ?? 'Non défini' }}</x-filament::badge>
                    </div>
                    @if($record->current_level_entered_at)
                        <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 dark:border-white/10">
                            <span class="text-gray-500 dark:text-gray-400">Entré dans ce palier</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->current_level_entered_at->format('d/m/Y') }}</span>
                        </div>
                    @endif
                    @if($record->exitLevel)
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Palier de sortie</span>
                            <x-filament::badge color="success">{{ $record->exitLevel->name }}</x-filament::badge>
                        </div>
                    @endif
                </div>

                @if($cohort)
                    <div class="rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/80 dark:border-white/10 p-3.5 text-xs space-y-1">
                        <span class="block text-[10px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Cohorte rattachée</span>
                        <span class="block font-bold text-gray-900 dark:text-white">{{ $cohort->name }}</span>
                        @if($cohort->program)
                            <span class="block text-gray-500 dark:text-gray-400">{{ $cohort->program->name }}</span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Coaches assignés --}}
            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-user-group class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Mentors &amp; Coaches</h3>
                    </div>
                    <x-filament::badge color="success">{{ $coachesCount }}</x-filament::badge>
                </div>

                @if($activeAssignments->isNotEmpty())
                    <ul class="space-y-2.5">
                        @foreach($activeAssignments as $assignment)
                            @if($assignment->coach)
                                <li class="flex items-center gap-3 p-2.5 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/70 dark:border-white/10">
                                    <div class="w-8 h-8 rounded-lg bg-primary-600 text-white font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($assignment->coach->name ?? $assignment->coach->full_name ?? 'C', 0, 2)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                            {{ $assignment->coach->name ?? $assignment->coach->full_name ?? '' }}
                                        </p>
                                        @if($assignment->role)
                                            <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $assignment->role }}</p>
                                        @endif
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Aucun coach actif assigné à ce projet pour le moment.
                    </p>
                @endif
            </div>

            {{-- Historique des paliers (si présent) --}}
            @if($record->levelHistories && $record->levelHistories->isNotEmpty())
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-6 shadow-xs space-y-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">Historique des paliers</h3>
                    <div class="space-y-2.5">
                        @foreach($record->levelHistories->take(5) as $history)
                            <div class="text-xs p-2.5 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200/70 dark:border-white/10 space-y-0.5">
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $history->fromLevel?->name ?? 'Départ' }} &rarr; {{ $history->toLevel?->name ?? '—' }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $history->created_at?->format('d/m/Y') }}
                                    @if($history->validator)
                                        &bull; par {{ $history->validator->name }}
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
