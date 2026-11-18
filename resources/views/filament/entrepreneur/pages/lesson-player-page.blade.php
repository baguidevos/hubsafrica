<x-filament-panels::page>
    @if ($this->currentLesson)
        <x-courses.lesson-player
            :lesson="$this->currentLesson"
            :course="$this->course"
            :back-url="\App\Filament\Entrepreneur\Pages\CourseShowPage::getUrl(['course' => $this->course?->slug])"
            back-label="Retour au cours"
            :completed-ids="$this->completedLessonIds"
            :is-complete="$this->isComplete"
            :is-preview="false"
            :previous-url="$this->previousLesson ? \App\Filament\Entrepreneur\Pages\LessonPlayerPage::getUrl(['lesson' => $this->previousLesson->slug]) : null"
            :previous-title="$this->previousLesson?->title"
            :next-url="$this->nextLesson ? \App\Filament\Entrepreneur\Pages\LessonPlayerPage::getUrl(['lesson' => $this->nextLesson->slug]) : null"
            :next-title="$this->nextLesson?->title"
            :lesson-url-resolver="fn ($item) => \App\Filament\Entrepreneur\Pages\LessonPlayerPage::getUrl(['lesson' => $item->slug])"
            :entrepreneur="$this->entrepreneur"
        />
    @endif
</x-filament-panels::page>
