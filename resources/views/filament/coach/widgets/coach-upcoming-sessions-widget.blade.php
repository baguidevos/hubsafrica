<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-calendar-days class="w-5 h-5 text-indigo-600" />
                    <span>Mon agenda de coaching à venir</span>
                </div>
                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                    Toutes mes séances &rarr;
                </a>
            </div>
        </x-slot>

        @php
            $sessions = $this->getCoachSessions();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-2">
            @forelse($sessions as $s)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm transition hover:shadow-md {{ $s['accent'] ? 'border-indigo-300 dark:border-indigo-800 bg-indigo-50/20' : '' }}">
                    <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl border bg-white dark:bg-stone-800 leading-none shadow-sm {{ $s['accent'] ? 'border-indigo-300 text-indigo-600' : 'border-stone-200 text-stone-600' }}">
                        <span class="text-[9px] font-bold uppercase tracking-wider">{{ $s['dayLabel'] }}</span>
                        <span class="mt-0.5 text-sm font-black">{{ $s['dayNum'] }}</span>
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-stone-900 dark:text-white">{{ $s['theme'] }}</p>
                        <p class="truncate text-[11px] text-stone-600 dark:text-stone-300 font-medium">{{ $s['project'] }}</p>
                        <p class="truncate text-[10px] text-stone-400">Porteur : {{ $s['entrepreneur'] }}</p>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-xs font-bold tabular-nums text-indigo-600">{{ $s['time'] }}</p>
                        <p class="text-[10px] text-stone-400">{{ $s['duration'] }}</p>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center p-8 border border-dashed rounded-xl border-stone-200 dark:border-stone-800 text-center">
                    <x-heroicon-o-calendar-days class="w-10 h-10 text-stone-300 dark:text-stone-600 mb-2" />
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Aucune séance programmée pour le moment.</p>
                    <p class="text-xs text-stone-400 mt-1">Vos séances avec les entrepreneurs assignés s'afficheront ici.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
