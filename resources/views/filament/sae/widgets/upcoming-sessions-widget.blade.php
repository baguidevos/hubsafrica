<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-calendar-days class="w-5 h-5 text-teal-600" />
                    <span>Agenda — Prochaines séances</span>
                </div>
                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                    Voir tout &rarr;
                </a>
            </div>
        </x-slot>

        @php
            $sessions = $this->getUpcomingSessions();
        @endphp

        <div class="space-y-2 pt-1">
            @forelse($sessions as $s)
                <div class="flex items-center gap-3 p-2.5 rounded-lg border transition hover:shadow-sm {{ $s['accent'] ? 'border-teal-200 bg-teal-50/50 dark:border-teal-900 dark:bg-teal-950/20' : 'border-stone-200 dark:border-stone-800' }}">
                    <div class="flex h-11 w-11 shrink-0 flex-col items-center justify-center rounded-lg border leading-none shadow-sm {{ $s['accent'] ? 'border-teal-300 dark:border-teal-700' : 'border-stone-200 dark:border-stone-700' }}">
                        <span class="text-[9px] font-bold uppercase tracking-wider {{ $s['accent'] ? 'text-teal-600 dark:text-teal-400' : 'text-stone-400' }}">{{ $s['dayLabel'] }}</span>
                        <span class="mt-0.5 text-xs font-black text-stone-800 dark:text-stone-200">{{ $s['dayNum'] }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-stone-900 dark:text-white">{{ $s['theme'] }}</p>
                        <p class="truncate text-[11px] text-stone-500 dark:text-stone-400">
                            <span class="font-medium text-stone-700 dark:text-stone-300">{{ $s['coach'] }}</span> &bull; {{ $s['project'] }}
                        </p>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-xs font-bold tabular-nums text-teal-600 dark:text-teal-400">{{ $s['time'] }}</p>
                        <p class="text-[10px] text-stone-400">{{ $s['duration'] }}</p>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center p-6 border border-dashed rounded-lg border-stone-200 dark:border-stone-800 text-center">
                    <x-heroicon-o-calendar class="w-8 h-8 text-stone-300 dark:text-stone-600 mb-2" />
                    <p class="text-xs text-stone-400">Aucune séance planifiée prochainement.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
