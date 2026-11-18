<x-filament-panels::page>
    @php
        $courses = $this->courses;
        $completedIds = $this->completedLessonIds;
    @endphp

    @if ($courses->isEmpty())
        <x-filament::section>
            <div class="text-center py-10">
                <x-heroicon-o-play-circle class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Aucun cours disponible pour le moment</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Votre structure d'accompagnement publiera bientôt des modules de formation adaptés à votre palier.
                </p>
            </div>
        </x-filament::section>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($courses as $course)
                @php
                    $courseUnlocked = $this->isCourseUnlocked($course);
                    $allLessons = $course->modules->flatMap(fn ($m) => $m->lessons);
                    $totalLessons = $allLessons->count();
                    $completedCount = $allLessons->filter(fn ($l) => in_array($l->id, $completedIds))->count();
                    $progressPct = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;
                    $courseUrl = $this->getCourseUrl($course);
                @endphp

                <x-filament::section>
                    <div class="flex flex-col justify-between h-full gap-4">
                        <div class="space-y-3">
                            @if ($course->thumbnail)
                                <a href="{{ $courseUrl }}" class="block h-44 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                                </a>
                            @endif

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
                                        Verrouillé
                                    </x-filament::badge>
                                @endif
                            </div>

                            <div>
                                <a href="{{ $courseUrl }}" class="text-lg font-bold text-gray-950 dark:text-white hover:text-primary-600 dark:hover:text-primary-400">
                                    {{ $course->title }}
                                </a>
                                @if ($course->description)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 line-clamp-2">{{ $course->description }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200 dark:border-white/10 space-y-3">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-medium text-gray-600 dark:text-gray-400">
                                    <span>Progression</span>
                                    <span>{{ $completedCount }} / {{ $totalLessons }} ({{ $progressPct }}%)</span>
                                </div>
                                <div class="w-full h-2 rounded-full bg-gray-200 dark:bg-white/10 overflow-hidden">
                                    <div class="h-full bg-emerald-500 rounded-full transition-all" style="width: {{ $progressPct }}%"></div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <x-filament::button
                                    :href="$courseUrl"
                                    tag="a"
                                    :color="$courseUnlocked ? 'primary' : 'gray'"
                                    size="sm"
                                    icon="heroicon-o-book-open"
                                >
                                    {{ $courseUnlocked ? 'Voir le cours' : 'Consulter le programme' }}
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
