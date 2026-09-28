<x-filament-panels::page>
    @php
        $project = $this->record;
        $statusColors = [
            'ACTIF' => 'emerald',
            'BLOQUE' => 'red',
            'STAGNANT' => 'amber',
            'SORTI' => 'gray',
            'ABANDONNE' => 'gray',
            'DIPLOME' => 'emerald',
        ];
        $statusColor = $statusColors[$project->status?->value ?? ''] ?? 'gray';
    @endphp

    <div class="space-y-6">

        {{-- Hero Card --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-br from-emerald-500 via-emerald-600 to-teal-700 dark:from-emerald-700 dark:via-emerald-800 dark:to-teal-900"></div>
            <div class="relative px-6 pb-6 pt-20 sm:px-8">
                <div class="flex flex-col items-start gap-5 sm:flex-row sm:items-end">
                    {{-- Logo --}}
                    <div class="flex-shrink-0 -mt-4">
                        @if($project->logo)
                            <img src="{{ Storage::disk('public')->url($project->logo) }}"
                                 alt="{{ $project->name }}"
                                 class="h-24 w-24 rounded-2xl border-4 border-white bg-white object-cover shadow-lg dark:border-gray-800 dark:bg-gray-800 sm:h-28 sm:w-28">
                        @else
                            <div class="flex h-24 w-24 items-center justify-center rounded-2xl border-4 border-white bg-emerald-100 shadow-lg dark:border-gray-800 dark:bg-emerald-900/30 sm:h-28 sm:w-28">
                                <x-heroicon-o-light-bulb class="h-12 w-12 text-emerald-600 dark:text-emerald-400" />
                            </div>
                        @endif
                    </div>

                    {{-- Name & Status --}}
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                            {{ $project->name }}
                        </h2>
                        @if($project->pitch)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                {{ $project->pitch }}
                            </p>
                        @endif
                    </div>

                    {{-- Status Badge --}}
                    @if($project->status)
                        <div class="flex-shrink-0">
                            <span @class([
                                'inline-flex items-center gap-x-1.5 rounded-full px-3 py-1.5 text-sm font-semibold',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400' => $statusColor === 'emerald',
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' => $statusColor === 'red',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400' => $statusColor === 'amber',
                                'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' => $statusColor === 'gray',
                            ])>
                                @if($project->status->getIcon())
                                    <x-dynamic-component :component="$project->status->getIcon()" class="h-4 w-4" />
                                @endif
                                {{ $project->status->getLabel() }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Key Metrics --}}
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            {{-- Secteur --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <x-heroicon-o-building-storefront class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Secteur</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $project->sector ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Territoire --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                        <x-heroicon-o-map-pin class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Territoire</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $project->territory ?? '—' }}</p>
                    </div>
                </div>
            </div>

            {{-- Équipe --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-900/30">
                        <x-heroicon-o-users class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Équipe</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $project->team_size ?? 1 }} personne{{ ($project->team_size ?? 1) > 1 ? 's' : '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Niveau --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/30">
                        <x-heroicon-o-chart-bar class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Niveau</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $project->currentLevel?->name ?? 'Non défini' }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Two Column Layout --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left Column: Main Info (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Proposition de valeur --}}
                @if($project->value_proposition)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-sparkles class="h-5 w-5 text-emerald-500" />
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Proposition de valeur</h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $project->value_proposition }}</p>
                    </div>
                @endif

                {{-- Problème & Solution --}}
                @if($project->problem || $project->solution)
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        @if($project->problem)
                            <div class="rounded-xl border border-red-100 bg-red-50/50 p-6 dark:border-red-900/30 dark:bg-red-900/10">
                                <div class="flex items-center gap-2 mb-3">
                                    <x-heroicon-o-exclamation-triangle class="h-5 w-5 text-red-500 dark:text-red-400" />
                                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-300">Problème identifié</h3>
                                </div>
                                <p class="text-sm leading-relaxed text-red-700 dark:text-red-300/80">{{ $project->problem }}</p>
                            </div>
                        @endif
                        @if($project->solution)
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-6 dark:border-emerald-900/30 dark:bg-emerald-900/10">
                                <div class="flex items-center gap-2 mb-3">
                                    <x-heroicon-o-check-badge class="h-5 w-5 text-emerald-500 dark:text-emerald-400" />
                                    <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Solution proposée</h3>
                                </div>
                                <p class="text-sm leading-relaxed text-emerald-700 dark:text-emerald-300/80">{{ $project->solution }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Produits & Services --}}
                @if($project->product_service)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-cube class="h-5 w-5 text-blue-500" />
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Produits & Services</h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300">{{ $project->product_service }}</p>
                    </div>
                @endif

                {{-- Description / Historique --}}
                @if($project->description)
                    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-2 mb-4">
                            <x-heroicon-o-document-text class="h-5 w-5 text-gray-500" />
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Historique & Présentation</h3>
                        </div>
                        <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $project->description }}</p>
                    </div>
                @endif
            </div>

            {{-- Right Column: Sidebar (1/3) --}}
            <div class="space-y-6">

                {{-- Marché & Cible --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Marché & Cible</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Cible clientèle</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->target ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Marché visé</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->market ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Accompagnement --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Accompagnement</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Cohorte active</dt>
                            <dd class="mt-1">
                                @if($project->latestEnrollment?->cohort)
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                        {{ $project->latestEnrollment->cohort->name }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-500">Non assignée</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Coach assigné</dt>
                            <dd class="mt-1">
                                @php
                                    $activeAssignment = $project->assignments?->first(fn ($a) => $a->status === 'active' || !$a->ended_at);
                                @endphp
                                @if($activeAssignment?->coach)
                                    <div class="flex items-center gap-2">
                                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                                            <x-heroicon-o-user class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $activeAssignment->coach->full_name ?? $activeAssignment->coach->name ?? '—' }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 dark:text-gray-500">Aucun coach assigné</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Sessions de coaching</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->sessions?->count() ?? 0 }} session{{ ($project->sessions?->count() ?? 0) > 1 ? 's' : '' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Livrables soumis</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->submissions?->count() ?? 0 }} livrable{{ ($project->submissions?->count() ?? 0) > 1 ? 's' : '' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Dates --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Dates clés</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Intégration</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->created_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        @if($project->current_level_entered_at)
                            <div>
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Entrée niveau actuel</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->current_level_entered_at->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">Dernière mise à jour</dt>
                            <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $project->updated_at?->format('d/m/Y à H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Pitch section (full width, at bottom if long) --}}
        @if($project->pitch && strlen($project->pitch) > 100)
            <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-6 shadow-sm dark:border-emerald-900/30 dark:from-emerald-900/20 dark:to-teal-900/20">
                <div class="flex items-center gap-2 mb-4">
                    <x-heroicon-o-megaphone class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    <h3 class="text-base font-semibold text-emerald-900 dark:text-emerald-200">Pitch Elevator</h3>
                </div>
                <blockquote class="text-sm italic leading-relaxed text-emerald-800 dark:text-emerald-300 border-l-4 border-emerald-300 pl-4 dark:border-emerald-600">
                    « {{ $project->pitch }} »
                </blockquote>
            </div>
        @endif

    </div>
</x-filament-panels::page>
