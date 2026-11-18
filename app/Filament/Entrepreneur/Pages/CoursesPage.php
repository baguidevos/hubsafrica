<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\Entrepreneur;
use App\Models\LessonProgress;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use UnitEnum;

class CoursesPage extends Page
{
    protected string $view = 'filament.entrepreneur.pages.courses-page';

    protected static string|UnitEnum|null $navigationGroup = 'Mon espace';

    protected static ?string $navigationLabel = 'Mes cours';

    protected static ?string $title = 'Mes cours & Parcours de formation';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'mes-cours';

    #[Computed]
    public function entrepreneur(): ?Entrepreneur
    {
        return auth()->user()?->entrepreneurProfile;
    }

    #[Computed]
    public function courses(): Collection
    {
        return Course::query()
            ->where('is_published', true)
            ->orderBy('sort_order')
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
            ->get();
    }

    #[Computed]
    public function completedLessonIds(): array
    {
        return LessonProgress::where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->pluck('course_lesson_id')
            ->all();
    }

    public function isCourseUnlocked(Course $course): bool
    {
        return $course->isAccessibleBy($this->entrepreneur);
    }

    public function isLessonUnlocked(CourseLesson $lesson): bool
    {
        return $lesson->isAccessibleBy($this->entrepreneur);
    }

    public function getCourseUrl(Course $course): string
    {
        return CourseShowPage::getUrl(['course' => $course->slug]);
    }

    public function getLessonUrl(CourseLesson $lesson): string
    {
        return LessonPlayerPage::getUrl(['lesson' => $lesson->slug]);
    }
}
