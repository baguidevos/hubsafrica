<x-filament-widgets::widget>
    @php
        $distribution = $this->getDistributionData();
        $allApplicationsUrl = $this->getApplicationsUrl();
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            <div class="flex flex-wrap items-center justify-between gap-3 w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-rectangle-stack class="w-5 h-5 text-teal-600" />
                    <span>Répartition des candidatures par programme et cohorte</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if (count($distribution['program_options']) > 1)
                        <select
                            wire:model.live="selectedProgramId"
                            aria-label="Filtrer par programme"
                            class="rounded-lg border border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-2.5 py-1 text-xs font-medium text-stone-700 dark:text-stone-200 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        >
                            <option value="">Tous les programmes ({{ $distribution['programs_count'] }})</option>
                            @foreach ($distribution['program_options'] as $programId => $programName)
                                <option value="{{ $programId }}">{{ $programName }}</option>
                            @endforeach
                        </select>
                    @endif

                    @if ($allApplicationsUrl)
                        <a
                            href="{{ $allApplicationsUrl }}"
                            class="text-xs font-medium text-teal-600 hover:text-teal-700 dark:text-teal-400 dark:hover:text-teal-300"
                        >
                            Voir les candidatures &rarr;
                        </a>
                    @endif
                </div>
            </div>
        </x-slot>

        <x-slot name="description">
            Ventilation des candidatures reçues (hors brouillons) par programme et par cohorte
        </x-slot>

        @if ($distribution['total_applications'] === 0)
            <p class="py-6 text-center text-xs text-stone-400">
                Aucune candidature reçue pour le moment.
            </p>
        @else
            <div class="mb-4 flex flex-wrap items-center gap-2 text-xs">
                <span class="inline-flex items-center rounded-full bg-teal-50 dark:bg-teal-950/50 px-2.5 py-1 font-semibold text-teal-700 dark:text-teal-300 tabular-nums">
                    {{ $distribution['total_applications'] }} candidature{{ $distribution['total_applications'] > 1 ? 's' : '' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-stone-100 dark:bg-stone-800 px-2.5 py-1 font-medium text-stone-700 dark:text-stone-300 tabular-nums">
                    {{ $distribution['programs_count'] }} programme{{ $distribution['programs_count'] > 1 ? 's' : '' }}
                </span>
                <span class="inline-flex items-center rounded-full bg-stone-100 dark:bg-stone-800 px-2.5 py-1 font-medium text-stone-700 dark:text-stone-300 tabular-nums">
                    {{ $distribution['cohorts_count'] }} cohorte{{ $distribution['cohorts_count'] > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="grid grid-cols-1 gap-4 {{ count($distribution['programs']) > 1 ? 'lg:grid-cols-2' : '' }}">
                @foreach ($distribution['programs'] as $program)
                    @php
                        $programUrl = $program['program_id'] !== null
                            ? $this->getApplicationsUrl($program['program_id'])
                            : null;
                    @endphp

                    <div class="rounded-xl border border-stone-200 dark:border-stone-800 p-4 space-y-3">
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    @if ($programUrl)
                                        <a
                                            href="{{ $programUrl }}"
                                            class="text-sm font-semibold text-stone-800 dark:text-stone-100 hover:text-teal-600 dark:hover:text-teal-400 truncate block"
                                            title="{{ $program['program_name'] }}"
                                        >
                                            {{ $program['program_name'] }}
                                        </a>
                                    @else
                                        <p class="text-sm font-semibold text-stone-800 dark:text-stone-100 truncate" title="{{ $program['program_name'] }}">
                                            {{ $program['program_name'] }}
                                        </p>
                                    @endif
                                </div>

                                <span class="shrink-0 text-xs font-semibold tabular-nums text-stone-700 dark:text-stone-300">
                                    {{ $program['total'] }} &bull; {{ $program['share'] }}%
                                </span>
                            </div>

                            <div class="w-full h-2 rounded-full bg-stone-100 dark:bg-stone-800 overflow-hidden">
                                <div
                                    class="h-2 rounded-full bg-teal-600 transition-all"
                                    style="width: {{ min(100, $program['share']) }}%"
                                ></div>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-[11px] tabular-nums">
                                <span class="inline-flex items-center rounded-md bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 font-medium text-emerald-700 dark:text-emerald-300">
                                    {{ $program['accepted'] }} admise{{ $program['accepted'] > 1 ? 's' : '' }}
                                </span>
                                <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-950/50 px-2 py-0.5 font-medium text-amber-700 dark:text-amber-300">
                                    {{ $program['pending'] }} à traiter
                                </span>
                                @if ($program['waitlisted'] > 0)
                                    <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-950/50 px-2 py-0.5 font-medium text-purple-700 dark:text-purple-300">
                                        {{ $program['waitlisted'] }} en attente
                                    </span>
                                @endif
                                @if ($program['rejected'] > 0)
                                    <span class="inline-flex items-center rounded-md bg-rose-50 dark:bg-rose-950/50 px-2 py-0.5 font-medium text-rose-700 dark:text-rose-300">
                                        {{ $program['rejected'] }} refusée{{ $program['rejected'] > 1 ? 's' : '' }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2.5 pt-2.5 border-t border-stone-100 dark:border-stone-800">
                            @foreach ($program['cohorts'] as $cohort)
                                @php
                                    $cohortUrl = $cohort['cohort_id'] !== null
                                        ? $this->getApplicationsUrl($program['program_id'], $cohort['cohort_id'])
                                        : null;
                                @endphp

                                <div class="space-y-1">
                                    <div class="flex items-center justify-between gap-2 text-xs">
                                        <div class="min-w-0">
                                            @if ($cohortUrl)
                                                <a
                                                    href="{{ $cohortUrl }}"
                                                    class="font-medium text-stone-700 dark:text-stone-200 hover:text-teal-600 dark:hover:text-teal-400 truncate block"
                                                    title="{{ $cohort['cohort_name'] }}"
                                                >
                                                    {{ $cohort['cohort_name'] }}
                                                </a>
                                            @else
                                                <span class="italic text-stone-500 dark:text-stone-400 truncate block" title="{{ $cohort['cohort_name'] }}">
                                                    {{ $cohort['cohort_name'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <span class="shrink-0 tabular-nums font-medium text-stone-600 dark:text-stone-400">
                                            {{ $cohort['total'] }} ({{ $cohort['program_share'] }}%)
                                        </span>
                                    </div>

                                    <div class="w-full h-1.5 rounded-full bg-stone-100 dark:bg-stone-800 overflow-hidden">
                                        <div
                                            class="h-1.5 rounded-full bg-amber-500 transition-all"
                                            style="width: {{ min(100, $cohort['program_share']) }}%"
                                        ></div>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-2.5 gap-y-0.5 text-[10px] text-stone-500 dark:text-stone-400 tabular-nums">
                                        <span>{{ $cohort['accepted'] }} admise{{ $cohort['accepted'] > 1 ? 's' : '' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $cohort['pending'] }} à traiter</span>
                                        @if ($cohort['waitlisted'] > 0)
                                            <span>&bull;</span>
                                            <span>{{ $cohort['waitlisted'] }} en attente</span>
                                        @endif
                                        @if ($cohort['rejected'] > 0)
                                            <span>&bull;</span>
                                            <span>{{ $cohort['rejected'] }} refusée{{ $cohort['rejected'] > 1 ? 's' : '' }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
