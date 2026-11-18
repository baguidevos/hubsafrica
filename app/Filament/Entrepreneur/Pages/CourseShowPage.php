<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Entrepreneur;
use App\Models\LessonProgress;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class CourseShowPage extends Page
{
    protected string $view = 'filament.entrepreneur.pages.course-show-page';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'mes-cours/voir';

    #[Url]
    public ?string $course = null;

    public ?Course $currentCourse = null;

    public function mount(): void
    {
        if (blank($this->course)) {
            $this->redirect(CoursesPage::getUrl());

            return;
        }

        $course = Course::query()
            ->where('slug', $this->course)
            ->where('is_published', true)
            ->with([
                'level',
                'modules' => function ($query) {
                    $query->where('is_published', true)
                        ->orderBy('sort_order')
                        ->with([
                            'lessons' => fn ($q) => $q->where('is_published', true)->orderBy('sort_order'),
                        ]);
                },
            ])
            ->first();

        if (! $course) {
            Notification::make()
                ->title('Cours introuvable')
                ->warning()
                ->send();

            $this->redirect(CoursesPage::getUrl());

            return;
        }

        $this->currentCourse = $course;
    }

    public function getTitle(): string
    {
        return $this->currentCourse?->title ?? 'Détail du cours';
    }

    #[Computed]
    public function entrepreneur(): ?Entrepreneur
    {
        return auth()->user()?->entrepreneurProfile;
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
    public function isCourseUnlocked(): bool
    {
        return $this->currentCourse?->isAccessibleBy($this->entrepreneur) ?? false;
    }

    #[Computed]
    public function nextAvailableLesson(): ?CourseLesson
    {
        if (! $this->currentCourse) {
            return null;
        }

        $completedIds = $this->completedLessonIds;
        $accessible = $this->currentCourse->modules
            ->flatMap(fn ($m) => $m->lessons)
            ->filter(fn (CourseLesson $l) => $l->isAccessibleBy($this->entrepreneur));

        return $accessible->first(fn (CourseLesson $l) => ! in_array($l->id, $completedIds))
            ?? $accessible->first();
    }

    public function isLessonUnlocked(CourseLesson $lesson): bool
    {
        return $lesson->isAccessibleBy($this->entrepreneur);
    }

    public function getLessonUrl(CourseLesson $lesson): string
    {
        return LessonPlayerPage::getUrl(['lesson' => $lesson->slug]);
    }
}
