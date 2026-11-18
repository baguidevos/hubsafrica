@props([
    'lesson',
    'course',
    'backUrl',
    'backLabel' => 'Retour au cours',
    'completedIds' => [],
    'isComplete' => false,
    'isPreview' => false,
    'previousUrl' => null,
    'previousTitle' => null,
    'nextUrl' => null,
    'nextTitle' => null,
    'lessonUrlResolver',
    'entrepreneur' => null,
])

@php
    $embedUrl = $lesson->getEmbedVideoUrl();
    $videoFileUrl = $lesson->getVideoFileUrl();
    $attachmentUrl = $lesson->getAttachmentUrl();
@endphp

<div class="space-y-6">
    @if ($isPreview)
        <x-filament::section compact>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2 text-xs font-medium text-primary-600 dark:text-primary-400">
                    <x-heroicon-o-eye class="w-4 h-4 shrink-0" />
                    <span>Mode prévisualisation — Rendu exact de la leçon côté entrepreneur</span>
                </div>
                <x-filament::badge :color="$lesson->is_published ? 'success' : 'gray'" size="sm">
                    {{ $lesson->is_published ? 'Publiée' : 'Brouillon' }}
                </x-filament::badge>
            </div>
        </x-filament::section>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <x-filament::button
            :href="$backUrl"
            tag="a"
            color="gray"
            size="sm"
            icon="heroicon-o-arrow-left"
        >
            {{ $backLabel }}
        </x-filament::button>

        @if (! $isPreview)
            <x-filament::button
                wire:click="toggleComplete"
                :color="$isComplete ? 'success' : 'primary'"
                size="sm"
                :icon="$isComplete ? 'heroicon-s-check-circle' : 'heroicon-o-check-circle'"
            >
                {{ $isComplete ? 'Leçon terminée' : 'Marquer comme terminée' }}
            </x-filament::button>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale : Vidéo, Notes & Support -->
        <div class="lg:col-span-2 space-y-6">
            @if ($embedUrl)
                <div class="aspect-video w-full overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 bg-gray-950">
                    <iframe
                        src="{{ $embedUrl }}"
                        class="w-full h-full"
                        frameborder="0"
                        allow="autoplay; fullscreen; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            @elseif ($videoFileUrl)
                <div class="aspect-video w-full overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 bg-gray-950">
                    <video src="{{ $videoFileUrl }}" controls class="w-full h-full"></video>
                </div>
            @endif

            <x-filament::section>
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-primary-600 dark:text-primary-400">
                            {{ $course?->title }} · {{ $lesson->module?->title }}
                        </span>
                        @if ($lesson->getFormattedDuration())
                            <x-filament::badge color="gray">
                                Durée : {{ $lesson->getFormattedDuration() }}
                            </x-filament::badge>
                        @endif
                    </div>

                    @if ($lesson->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $lesson->description }}</p>
                    @endif

                    @if ($attachmentUrl)
                        <div class="pt-2">
                            <x-filament::button
                                :href="$attachmentUrl"
                                tag="a"
                                target="_blank"
                                color="info"
                                size="sm"
                                icon="heroicon-o-document-arrow-down"
                            >
                                Télécharger le support de cours
                            </x-filament::button>
                        </div>
                    @endif

                    @if ($lesson->notes)
                        <div class="pt-4 border-t border-gray-200 dark:border-white/10 prose dark:prose-invert max-w-none text-sm">
                            {!! $lesson->notes !!}
                        </div>
                    @endif
                </div>
            </x-filament::section>

            <div class="flex items-center justify-between gap-4">
                @if ($previousUrl)
                    <x-filament::button
                        :href="$previousUrl"
                        tag="a"
                        color="gray"
                        icon="heroicon-o-chevron-left"
                    >
                        {{ $previousTitle }}
                    </x-filament::button>
                @else
                    <div></div>
                @endif

                @if ($nextUrl)
                    <x-filament::button
                        :href="$nextUrl"
                        tag="a"
                        color="primary"
                        icon-position="after"
                        icon="heroicon-o-chevron-right"
                    >
                        {{ $nextTitle }}
                    </x-filament::button>
                @endif
            </div>
        </div>

        <!-- Sidebar : Programme du cours -->
        <div class="space-y-4">
            <x-filament::section>
                <x-slot name="heading">Programme du cours</x-slot>

                <div class="space-y-4">
                    @foreach ($course?->modules ?? [] as $module)
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">
                                {{ $module->title }}
                            </h4>
                            <div class="space-y-1">
                                @foreach ($module->lessons as $item)
                                    @php
                                        $isCurrent = $item->id === $lesson->id;
                                        $itemUnlocked = $isPreview || $item->isAccessibleBy($entrepreneur);
                                        $itemDone = in_array($item->id, $completedIds);
                                    @endphp
                                    @if ($itemUnlocked)
                                        <a
                                            href="{{ $lessonUrlResolver($item) }}"
                                            @class([
                                                'flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs transition',
                                                'bg-primary-500/10 text-primary-600 dark:text-primary-400 font-semibold' => $isCurrent,
                                                'hover:bg-gray-500/10 text-gray-700 dark:text-gray-300' => ! $isCurrent,
                                            ])
                                        >
                                            <span class="truncate">{{ $item->title }}</span>
                                            @if ($itemDone)
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-emerald-500 shrink-0" />
                                            @endif
                                        </a>
                                    @else
                                        <div class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-xs text-gray-400">
                                            <span class="truncate">{{ $item->title }}</span>
                                            <x-heroicon-o-lock-closed class="w-4 h-4 shrink-0" />
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        </div>
    </div>
</div>
