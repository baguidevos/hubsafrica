<x-filament-panels::page>
    @if ($this->currentCourse)
        @php
            $course = $this->currentCourse;
            $courseUnlocked = $this->isCourseUnlocked;
            $completedIds = $this->completedLessonIds;
            $allLessons = $course->modules->flatMap(fn ($m) => $m->lessons);
            $totalLessons = $allLessons->count();
            $completedCount = $allLessons->filter(fn ($l) => in_array($l->id, $completedIds))->count();
            $progressPct = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
            $nextLesson = $this->nextAvailableLesson;
        @endphp

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <x-filament::button
                :href="\App\Filament\Entrepreneur\Pages\CoursesPage::getUrl()"
                tag="a"
                color="gray"
                size="sm"
                icon="heroicon-o-arrow-left"
            >
                Retour au catalogue
            </x-filament::button>

            @if ($nextLesson)
                <x-filament::button
                    :href="$this->getLessonUrl($nextLesson)"
                    tag="a"
                    color="primary"
                    size="sm"
                    icon="heroicon-o-play"
                >
                    {{ $completedCount > 0 ? 'Reprendre le cours' : 'Commencer le cours' }}
                </x-filament::button>
            @endif
        </div>

        <x-filament::section>
            <div class="flex flex-col lg:flex-row gap-6">
                @if ($course->thumbnail)
                    <div class="w-full lg:w-64 h-40 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 shrink-0">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="flex-1 flex flex-col justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($course->level)
                                <x-filament::badge :color="$courseUnlocked ? 'success' : 'warning'">
                                    Palier : {{ $course->level->name }}
                                </x-filament::badge>
                            @else
                                <x-filament::badge color="info">Accès tous paliers</x-filament::badge>
                            @endif

                            <x-filament::badge color="gray">
                                {{ $course->modules->count() }} module(s) · {{ $totalLessons }} leçon(s)
                            </x-filament::badge>

                            @if (! $courseUnlocked)
                                <x-filament::badge color="gray" icon="heroicon-m-lock-closed">
                                    Verrouillé pour votre palier actuel
                                </x-filament::badge>
                            @endif
                        </div>

                        @if ($course->description)
                            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                {{ $course->description }}
                            </p>
                        @endif
                    </div>

                    <div class="space-y-1.5 max-w-md">
                        <div class="flex items-center justify-between text-xs font-medium text-gray-600 dark:text-gray-400">
                            <span>Progression globale</span>
                            <span>{{ $completedCount }} / {{ $totalLessons }} leçons ({{ $progressPct }}%)</span>
                        </div>
                        <div class="w-full h-2.5 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $progressPct }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="mt-6 space-y-4">
            @forelse ($course->modules as $moduleIndex => $module)
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span>Module {{ $moduleIndex + 1 }} : {{ $module->title }}</span>
                            @if ($module->is_free)
                                <x-filament::badge color="info" size="sm">Aperçu libre</x-filament::badge>
                            @endif
                        </div>
                    </x-slot>

                    @if ($module->description)
                        <x-slot name="description">{{ $module->description }}</x-slot>
                    @endif

                    @if ($module->lessons->isEmpty())
                        <p class="text-xs text-gray-400 italic">Aucune leçon publiée dans ce module pour le moment.</p>
                    @else
                        <div class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach ($module->lessons as $lessonIndex => $lesson)
                                @php
                                    $lessonUnlocked = $this->isLessonUnlocked($lesson);
                                    $isCompleted = in_array($lesson->id, $completedIds);
                                @endphp
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if ($isCompleted)
                                            <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                                        @elseif ($lessonUnlocked)
                                            <x-heroicon-o-play-circle class="w-5 h-5 text-primary-500 shrink-0" />
                                        @else
                                            <x-heroicon-o-lock-closed class="w-5 h-5 text-gray-400 shrink-0" />
                                        @endif

                                        <div class="min-w-0">
                                            @if ($lessonUnlocked)
                                                <a href="{{ $this->getLessonUrl($lesson) }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 block truncate">
                                                    {{ $lessonIndex + 1 }}. {{ $lesson->title }}
                                                </a>
                                            @else
                                                <span class="text-sm font-medium text-gray-400 block truncate">
                                                    {{ $lessonIndex + 1 }}. {{ $lesson->title }}
                                                </span>
                                            @endif

                                            @if ($lesson->description)
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ $lesson->description }}</p>
                                            @endif
                                        </div>

                                        @if ($lesson->is_free && ! $courseUnlocked)
                                            <x-filament::badge color="info" size="sm">Aperçu gratuit</x-filament::badge>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3 shrink-0">
                                        @if ($lesson->getFormattedDuration())
                                            <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                                                {{ $lesson->getFormattedDuration() }}
                                            </span>
                                        @endif

                                        @if ($lessonUnlocked)
                                            <x-filament::button
                                                :href="$this->getLessonUrl($lesson)"
                                                tag="a"
                                                size="xs"
                                                :color="$isCompleted ? 'gray' : 'primary'"
                                            >
                                                {{ $isCompleted ? 'Revoir' : 'Suivre' }}
                                            </x-filament::button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-filament::section>
            @empty
                <x-filament::section>
                    <p class="text-sm text-gray-500 text-center py-4">Aucun module publié pour ce cours.</p>
                </x-filament::section>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>
