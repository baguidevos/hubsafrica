<x-filament-panels::page>
    @if ($this->currentLesson)
        <x-courses.lesson-player
            :lesson="$this->currentLesson"
            :course="$this->record"
            :back-url="\App\Filament\Sae\Resources\Courses\Pages\ViewCourse::getUrl(['record' => $this->record])"
            back-label="Retour au cours"
            :is-preview="true"
            :previous-url="$this->previousLesson ? $this->getLessonPreviewUrl($this->previousLesson) : null"
            :previous-title="$this->previousLesson?->title"
            :next-url="$this->nextLesson ? $this->getLessonPreviewUrl($this->nextLesson) : null"
            :next-title="$this->nextLesson?->title"
            :lesson-url-resolver="fn ($item) => $this->getLessonPreviewUrl($item)"
        />
    @endif
</x-filament-panels::page>
