<?php

namespace App\Filament\Coach\Resources\Courses\Pages;

use App\Filament\Coach\Resources\Courses\CourseResource;
use App\Models\CourseLesson;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class PreviewCourseLesson extends Page
{
    use InteractsWithRecord;

    protected static string $resource = CourseResource::class;

    protected string $view = 'filament.coach.resources.courses.pages.preview-course-lesson';

    #[Url]
    public ?string $lesson = null;

    public ?CourseLesson $currentLesson = null;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record)->load([
            'level',
            'modules' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order')->with([
                'lessons' => fn ($lq) => $lq->where('is_published', true)->orderBy('sort_order'),
            ]),
        ]);

        $allLessons = $this->orderedLessons;

        $this->currentLesson = filled($this->lesson)
            ? $allLessons->firstWhere('slug', $this->lesson)
            : $allLessons->first();

        if (! $this->currentLesson) {
            $this->redirect(ViewCourse::getUrl(['record' => $this->record]));
        }
    }

    public function getTitle(): string
    {
        return $this->currentLesson?->title ?? $this->record->title;
    }

    public function getBreadcrumbs(): array
    {
        return [
            CourseResource::getUrl('index') => 'Cours & Parcours',
            ViewCourse::getUrl(['record' => $this->record]) => $this->record->title,
            $this->getTitle(),
        ];
    }

    #[Computed]
    public function orderedLessons(): Collection
    {
        return $this->record->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->values();
    }

    #[Computed]
    public function previousLesson(): ?CourseLesson
    {
        if (! $this->currentLesson) {
            return null;
        }

        $lessons = $this->orderedLessons;
        $index = $lessons->search(fn (CourseLesson $item) => $item->id === $this->currentLesson->id);

        return $index !== false && $index > 0 ? $lessons[$index - 1] : null;
    }

    #[Computed]
    public function nextLesson(): ?CourseLesson
    {
        if (! $this->currentLesson) {
            return null;
        }

        $lessons = $this->orderedLessons;
        $index = $lessons->search(fn (CourseLesson $item) => $item->id === $this->currentLesson->id);

        return $index !== false && $index < $lessons->count() - 1 ? $lessons[$index + 1] : null;
    }

    public function getLessonPreviewUrl(CourseLesson $lesson): string
    {
        return static::getUrl([
            'record' => $this->record,
            'lesson' => $lesson->slug,
        ]);
    }
}
