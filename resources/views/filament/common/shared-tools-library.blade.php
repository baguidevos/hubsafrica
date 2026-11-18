<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="space-y-1">
                    <h2 class="text-base font-semibold">
                        Outils partagés par le réseau
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Accédez aux ressources pédagogiques et outils partagés selon le type de structure d'accompagnement.
                    </p>
                    @if ($this->viewerSaeTypes->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                Types de votre SAE :
                            </span>
                            @foreach ($this->viewerSaeTypes as $viewerType)
                                <x-filament::badge color="primary">
                                    {{ $viewerType->name }}
                                </x-filament::badge>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <x-filament::input.wrapper class="min-w-60">
                        <x-filament::input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Rechercher un outil..."
                        />
                    </x-filament::input.wrapper>

                    <x-filament::input.wrapper class="min-w-56">
                        <x-filament::input.select wire:model.live="selectedTypeFilter">
                            <option value="matching">Mes types de structure</option>
                            @foreach ($this->availableSaeTypes as $saeType)
                                <option value="{{ $saeType->id }}">{{ $saeType->name }}</option>
                            @endforeach
                            <option value="all">Tous les types</option>
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </x-filament::section>

        @if ($this->sharedTools->isEmpty())
            <x-filament::section>
                <div class="py-8 text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        Aucun outil partagé ne correspond à vos critères pour le moment.
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Essayez de changer le filtre de type de structure ou la recherche.
                    </p>
                </div>
            </x-filament::section>
        @else
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->sharedTools as $tool)
                    @php
                        $decision = $this->resolveAccessDecision($tool);
                    @endphp
                    <x-filament::section>
                        <div class="flex h-full flex-col justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($tool->format)
                                        <x-filament::badge color="info">
                                            {{ $tool->format->getLabel() }}
                                        </x-filament::badge>
                                    @endif

                                    @if ($tool->category)
                                        <x-filament::badge color="gray">
                                            {{ $tool->category->getLabel() }}
                                        </x-filament::badge>
                                    @endif

                                    @if ($tool->access_level)
                                        <x-filament::badge :color="$tool->access_level->getColor()">
                                            {{ $tool->access_level->getLabel() }}
                                        </x-filament::badge>
                                    @endif
                                </div>

                                <div>
                                    <h3 class="text-base font-semibold">
                                        {{ $tool->title }}
                                    </h3>
                                    @if ($tool->sae)
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                            Partagé par {{ $tool->sae->name }}
                                        </p>
                                    @endif
                                </div>

                                @if ($tool->description)
                                    <p class="line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                                        {{ $tool->description }}
                                    </p>
                                @endif

                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($tool->saeTypes as $saeType)
                                        <x-filament::badge color="primary" size="sm">
                                            {{ $saeType->name }}
                                        </x-filament::badge>
                                    @empty
                                        <x-filament::badge color="success" size="sm">
                                            Universel (tous types)
                                        </x-filament::badge>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-200 pt-3 dark:border-gray-700">
                                @if ($decision->canAccess)
                                    <x-filament::button
                                        tag="a"
                                        href="{{ route('public.outils.access', ['slug' => $tool->slug]) }}"
                                        target="_blank"
                                        icon="heroicon-o-arrow-down-tray"
                                        size="sm"
                                    >
                                        Accéder à l'outil
                                    </x-filament::button>
                                @else
                                    <x-filament::badge color="warning">
                                        {{ $decision->message }}
                                    </x-filament::badge>

                                    @if ($tool->access_level === \App\Enums\AccompanimentToolAccessLevel::ON_REQUEST && $decision->code !== 'REQUEST_PENDING')
                                        <form method="POST" action="{{ route('public.outils.request', ['slug' => $tool->slug]) }}">
                                            @csrf
                                            <x-filament::button type="submit" color="gray" size="sm">
                                                Demander l'accès
                                            </x-filament::button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </x-filament::section>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
