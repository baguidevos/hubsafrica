<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-calendar-days class="w-5 h-5 text-emerald-600" />
                    <span>Mes prochaines séances avec mon coach</span>
                </div>
                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                    Historique &rarr;
                </a>
            </div>
        </x-slot>

        @php
            $sessions = $this->getEntrepreneurSessions();
        @endphp

        <div class="space-y-3 pt-2">
            @forelse($sessions as $s)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm transition hover:shadow-md {{ $s['accent'] ? 'border-emerald-300 dark:border-emerald-800 bg-emerald-50/20' : '' }}">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl border bg-white dark:bg-stone-800 leading-none shadow-sm {{ $s['accent'] ? 'border-emerald-300 text-emerald-600' : 'border-stone-200 text-stone-600' }}">
                            <span class="text-[9px] font-bold uppercase tracking-wider">{{ $s['dayLabel'] }}</span>
                            <span class="mt-0.5 text-sm font-black">{{ $s['dayNum'] }}</span>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-stone-900 dark:text-white">{{ $s['theme'] }}</p>
                            <p class="text-[11px] text-stone-600 dark:text-stone-300">
                                Avec <span class="font-semibold">{{ $s['coach'] }}</span>
                                @if($s['coach_email'])
                                    <span class="text-stone-400">({{ $s['coach_email'] }})</span>
                                @endif
                            </p>
                            @if(!empty($s['objectives']))
                                <p class="text-[11px] text-stone-500 italic mt-0.5 line-clamp-1">&laquo; {{ $s['objectives'] }} &raquo;</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center sm:flex-col sm:items-end justify-between border-t sm:border-t-0 pt-2 sm:pt-0 border-stone-100 dark:border-stone-800">
                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 tabular-nums">{{ $s['time'] }}</span>
                        <span class="text-[10px] text-stone-400">{{ $s['duration'] }}</span>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center p-8 border border-dashed rounded-xl border-stone-200 dark:border-stone-800 text-center">
                    <x-heroicon-o-academic-cap class="w-10 h-10 text-stone-300 dark:text-stone-600 mb-2" />
                    <p class="text-sm font-medium text-stone-600 dark:text-stone-400">Aucune séance planifiée actuellement.</p>
                    <p class="text-xs text-stone-400 mt-1">Votre coach vous notifiera dès qu'un créneau d'accompagnement sera fixé.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
