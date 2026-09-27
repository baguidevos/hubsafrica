<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-sparkles class="w-5 h-5 text-amber-500" />
                    <span>Faits marquants récents</span>
                </div>
                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/timeline-events') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                    Historique &rarr;
                </a>
            </div>
        </x-slot>

        @php
            $events = $this->getRecentEvents();
        @endphp

        <div class="pt-1">
            @if(empty($events))
                <div class="flex flex-col items-center justify-center p-6 border border-dashed rounded-lg border-stone-200 dark:border-stone-800 text-center">
                    <x-heroicon-o-clock class="w-8 h-8 text-stone-300 dark:text-stone-600 mb-2" />
                    <p class="text-xs text-stone-400">Les événements du portefeuille apparaîtront ici.</p>
                </div>
            @else
                <ol class="relative border-l border-stone-200 dark:border-stone-800 ml-3 space-y-4 my-2">
                    @foreach($events as $e)
                        <li class="ml-4">
                            <div class="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white dark:border-stone-900 bg-teal-600"></div>
                            <p class="text-xs font-semibold text-stone-800 dark:text-stone-200">{{ $e['title'] }}</p>
                            @if(!empty($e['description']))
                                <p class="text-[11px] text-stone-500 dark:text-stone-400 line-clamp-1 mt-0.5">{{ $e['description'] }}</p>
                            @endif
                            <time class="block text-[10px] text-stone-400 mt-0.5">{{ $e['time_ago'] }}</time>
                        </li>
                    @endforeach
                </ol>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
