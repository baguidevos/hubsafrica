<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-m-chart-bar class="w-5 h-5 text-teal-600" />
                <span>Formules des principaux indicateurs de performance</span>
            </div>
        </x-slot>
        <x-slot name="description">
            Ratios d'efficacité et d'intensité de l'accompagnement calculés en temps réel
        </x-slot>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-7 pt-2">
            @foreach($this->getFormulesData() as $f)
                @php
                    $isGood = $f['raw'] >= 60;
                    $isWarning = $f['raw'] >= 30 && $f['raw'] < 60;
                    $toneClass = $isGood ? 'text-emerald-600' : ($isWarning ? 'text-amber-600' : 'text-rose-600');
                @endphp
                <div class="rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 p-3 shadow-sm transition hover:shadow-md flex flex-col justify-between">
                    <div>
                        <p class="text-[11px] font-semibold leading-tight text-stone-600 dark:text-stone-400 line-clamp-2 min-h-[2rem]">
                            {{ $f['label'] }}
                        </p>
                        <p class="mt-1 text-xl font-bold tabular-nums {{ $toneClass }}">
                            {{ $f['val'] }}
                        </p>
                    </div>

                    <div class="mt-3 pt-2 border-t border-stone-100 dark:border-stone-800 text-[10px] leading-tight text-stone-400 space-y-0.5">
                        <p class="font-medium text-stone-600 dark:text-stone-300 truncate" title="{{ $f['num'] }}">{{ $f['num'] }}</p>
                        <div class="mx-auto w-4 border-t border-stone-200 dark:border-stone-700"></div>
                        <p class="truncate" title="{{ $f['den'] }}">{{ $f['den'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
