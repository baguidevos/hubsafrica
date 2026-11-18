<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Entrepreneur;
use App\Models\LessonProgress;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class LessonPlayerPage extends Page
{
    protected string $view = 'filament.entrepreneur.pages.lesson-player-page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'mes-cours/lecon';

    #[Url]
    public ?string $lesson = null;

    public ?CourseLesson $currentLesson = null;

    public function mount(): void
    {
        if (blank($this->lesson)) {
            $this->redirect(CoursesPage::getUrl());

            return;
        }

        $lesson = CourseLesson::query()
            ->where('slug', $this->lesson)
            ->with(['module.course.level'])
            ->first();

        if (! $lesson || ! $lesson->isAccessibleBy($this->entrepreneur)) {
            Notification::make()
                ->title('Leçon verrouillée ou introuvable')
                ->body('Cette leçon n\'est pas accessible pour votre palier actuel.')
                ->warning()
                ->send();

            $this->redirect(CoursesPage::getUrl());

            return;
        }

        $this->currentLesson = $lesson;
    }

    public function getTitle(): string
    {
        return $this->currentLesson?->title ?? 'Lecture de la leçon';
    }

    #[Computed]
    public function entrepreneur(): ?Entrepreneur
    {
        return auth()->user()?->entrepreneurProfile;
    }

    #[Computed]
    public function course(): ?Course
    {
        return $this->currentLesson?->module?->course?->load([
            'modules' => function ($query) {
                $query->where('is_published', true)
                    ->orderBy('sort_order')
                    ->with([
                        'lessons' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order'),
                    ]);
            },
        ]);
    }

    #[Computed]
    public function completedLessonIds(): array
    {
        return LessonProgress::where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->pluck('course_lesson_id')
            ->all();
    }

    #[Computed]
    public function isComplete(): bool
    {
        return $this->currentLesson && in_array($this->currentLesson->id, $this->completedLessonIds);
    }

    #[Computed]
    public function accessibleLessons(): Collection
    {
        if (! $this->course) {
            return collect();
        }

        return $this->course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->filter(fn (CourseLesson $lesson) => $lesson->isAccessibleBy($this->entrepreneur))
            ->values();
    }

    #[Computed]
    public function previousLesson(): ?CourseLesson
    {
        if (! $this->currentLesson) {
            return null;
        }

        $lessons = $this->accessibleLessons;
        $index = $lessons->search(fn (CourseLesson $item) => $item->id === $this->currentLesson->id);

        return $index !== false && $index > 0 ? $lessons[$index - 1] : null;
    }

    #[Computed]
    public function nextLesson(): ?CourseLesson
    {
        if (! $this->currentLesson) {
            return null;
        }

        $lessons = $this->accessibleLessons;
        $index = $lessons->search(fn (CourseLesson $item) => $item->id === $this->currentLesson->id);

        return $index !== false && $index < $lessons->count() - 1 ? $lessons[$index + 1] : null;
    }

    public function toggleComplete(): void
    {
        if (! $this->currentLesson) {
            return;
        }

        $progress = LessonProgress::firstOrNew([
            'user_id' => auth()->id(),
            'course_lesson_id' => $this->currentLesson->id,
        ]);

        $progress->sae_id = $this->currentLesson->sae_id;
        $progress->completed_at = $progress->completed_at ? null : now();
        $progress->save();

        unset($this->completedLessonIds, $this->isComplete);
    }
}
