<x-filament-panels::page>
    @php
        $course = $this->record->loadMissing(['level', 'modules.lessons']);
        $totalLessons = $course->modules->sum(fn ($m) => $m->lessons->count());
        $isSaePanel = \Filament\Facades\Filament::getCurrentPanel()?->getId() === 'sae';
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            <div class="flex flex-col lg:flex-row gap-6">
                @if ($course->thumbnail)
                    <div class="w-full lg:w-64 h-40 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shrink-0">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="flex-1 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-filament::badge :color="$course->is_published ? 'success' : 'gray'">
                            {{ $course->is_published ? 'Publié' : 'Brouillon' }}
                        </x-filament::badge>
                        @if ($course->level)
                            <x-filament::badge color="primary">Palier : {{ $course->level->name }}</x-filament::badge>
                        @else
                            <x-filament::badge color="info">Tous paliers</x-filament::badge>
                        @endif
                        <x-filament::badge color="gray">
                            {{ $course->modules->count() }} module(s) · {{ $totalLessons }} leçon(s)
                        </x-filament::badge>
                    </div>

                    @if ($course->description)
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $course->description }}</p>
                    @endif
                </div>
            </div>
        </x-filament::section>

        @forelse ($course->modules as $moduleIndex => $module)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span>Module {{ $moduleIndex + 1 }} : {{ $module->title }}</span>
                            @if ($module->is_free)
                                <x-filament::badge color="info" size="sm">Aperçu libre</x-filament::badge>
                            @endif
                            @if (! $module->is_published)
                                <x-filament::badge color="gray" size="sm">Non publié</x-filament::badge>
                            @endif
                        </div>

                        @if ($isSaePanel)
                            <x-filament::button
                                :href="\App\Filament\Sae\Resources\CourseModules\CourseModuleResource::getUrl('edit', ['record' => $module])"
                                tag="a"
                                color="gray"
                                size="xs"
                                icon="heroicon-o-pencil-square"
                            >
                                Gérer les leçons
                            </x-filament::button>
                        @endif
                    </div>
                </x-slot>

                @if ($module->description)
                    <x-slot name="description">{{ $module->description }}</x-slot>
                @endif

                @if ($module->lessons->isEmpty())
                    <p class="text-xs text-gray-400 italic">Aucune leçon dans ce module.</p>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach ($module->lessons as $lessonIndex => $lesson)
                            @php
                                $previewUrl = $isSaePanel
                                    ? \App\Filament\Sae\Resources\Courses\Pages\PreviewCourseLesson::getUrl(['record' => $course, 'lesson' => $lesson->slug])
                                    : \App\Filament\Coach\Resources\Courses\Pages\PreviewCourseLesson::getUrl(['record' => $course, 'lesson' => $lesson->slug]);
                            @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="space-y-0.5 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ $previewUrl }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                            {{ $lessonIndex + 1 }}. {{ $lesson->title }}
                                        </a>
                                        @if ($lesson->is_free)
                                            <x-filament::badge color="info" size="sm">Aperçu</x-filament::badge>
                                        @endif
                                        @if (! $lesson->is_published)
                                            <x-filament::badge color="gray" size="sm">Brouillon</x-filament::badge>
                                        @endif
                                    </div>
                                    @if ($lesson->description)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $lesson->description }}</p>
                                    @endif
                                </div>

                                <div class="flex items-center gap-2 text-xs text-gray-500 shrink-0">
                                    @if ($lesson->video_url || $lesson->video_path)
                                        <x-filament::badge color="primary" size="sm">Vidéo</x-filament::badge>
                                    @endif
                                    @if ($lesson->attachment_path)
                                        <x-filament::badge color="info" size="sm">Support</x-filament::badge>
                                    @endif
                                    @if ($lesson->getFormattedDuration())
                                        <span class="tabular-nums">{{ $lesson->getFormattedDuration() }}</span>
                                    @endif
                                    <x-filament::button
                                        :href="$previewUrl"
                                        tag="a"
                                        color="primary"
                                        size="xs"
                                        icon="heroicon-o-eye"
                                    >
                                        {{ $isSaePanel ? 'Prévisualiser' : 'Consulter' }}
                                    </x-filament::button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-filament::section>
        @empty
            <x-filament::section>
                <p class="text-sm text-gray-500 text-center py-4">Aucun module créé pour ce cours.</p>
            </x-filament::section>
        @endforelse
    </div>
</x-filament-panels::page>
