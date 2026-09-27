<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center justify-between w-full">
                <div class="flex items-center gap-2">
                    <x-heroicon-m-user-group class="w-5 h-5 text-indigo-600" />
                    <span>Tableau de charge des coachs</span>
                </div>
                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaches') }}" class="text-xs text-teal-600 hover:text-teal-700 font-medium">
                    Gérer le réseau &rarr;
                </a>
            </div>
        </x-slot>

        @php
            $coaches = $this->getCoachWorkloads();
        @endphp

        <div class="space-y-3 pt-1">
            @forelse($coaches as $c)
                <div>
                    <div class="flex justify-between items-center text-xs mb-1">
                        <div>
                            <span class="font-medium text-stone-800 dark:text-stone-200">{{ $c['name'] }}</span>
                            <span class="text-[10px] text-stone-400"> ({{ $c['specialty'] }})</span>
                        </div>
                        <span class="tabular-nums font-semibold {{ $c['isOverloaded'] ? 'text-rose-600' : 'text-stone-600 dark:text-stone-400' }}">
                            {{ $c['actifs'] }} / {{ $c['capacite'] }} &bull; {{ $c['pct'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-stone-100 dark:bg-stone-800 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all {{ $c['isOverloaded'] ? 'bg-rose-500' : 'bg-teal-600' }}" style="width: {{ $c['pct'] }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-xs text-stone-400 py-3 text-center">Aucun coach actif enregistré.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
