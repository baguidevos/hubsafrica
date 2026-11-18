<?php

use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Filament\Coach\Resources\Courses\Pages\ListCourses as CoachListCourses;
use App\Filament\Coach\Resources\Courses\Pages\PreviewCourseLesson as CoachPreviewCourseLesson;
use App\Filament\Coach\Resources\Courses\Pages\ViewCourse as CoachViewCourse;
use App\Filament\Entrepreneur\Pages\CourseShowPage;
use App\Filament\Entrepreneur\Pages\CoursesPage;
use App\Filament\Entrepreneur\Pages\LessonPlayerPage;
use App\Filament\Sae\Resources\CourseModules\Pages\EditCourseModule;
use App\Filament\Sae\Resources\Courses\Pages\EditCourse;
use App\Filament\Sae\Resources\Courses\Pages\ListCourses as SaeListCourses;
use App\Filament\Sae\Resources\Courses\Pages\PreviewCourseLesson as SaePreviewCourseLesson;
use App\Filament\Sae\Resources\Courses\Pages\ViewCourse as SaeViewCourse;
use App\Models\Coach;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Entrepreneur;
use App\Models\LessonProgress;
use App\Models\Level;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('sae and coach panels render course management and read-only views', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Formation',
        'slug' => 'sae-formation-'.uniqid(),
    ]);

    $level = Level::create([
        'sae_id' => $sae->id,
        'name' => 'Idéation',
        'order' => 1,
    ]);

    $course = Course::create([
        'sae_id' => $sae->id,
        'level_id' => $level->id,
        'title' => 'Parcours Idéation',
        'is_published' => true,
    ]);

    $module = CourseModule::create([
        'sae_id' => $sae->id,
        'course_id' => $course->id,
        'title' => 'Module 1',
        'is_published' => true,
    ]);

    $lesson = CourseLesson::create([
        'sae_id' => $sae->id,
        'course_module_id' => $module->id,
        'title' => 'Leçon Intro',
        'is_published' => true,
    ]);

    $this->actingAs($owner);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('sae'));

    Livewire::test(SaeListCourses::class)->assertSuccessful()->assertSee('Parcours Idéation');
    Livewire::test(SaeViewCourse::class, ['record' => $course->getKey()])
        ->assertSuccessful()
        ->assertSee('Parcours Idéation')
        ->assertDontSee('Afficher Cours')
        ->assertSee('Module 1');
    Livewire::test(SaePreviewCourseLesson::class, ['record' => $course->getKey(), 'lesson' => $lesson->slug])
        ->assertSuccessful()
        ->assertSee('Mode prévisualisation')
        ->assertSee('Leçon Intro');
    Livewire::test(EditCourse::class, ['record' => $course->getKey()])
        ->assertSuccessful()
        ->assertSee('Modifier : Parcours Idéation');
    Livewire::test(EditCourseModule::class, ['record' => $module->getKey()])
        ->assertSuccessful()
        ->assertSee('Modifier : Module 1');

    $coachUser = User::factory()->create();
    $coachUser->saes()->attach($sae->id, ['role' => 'COACH', 'status' => 'ACTIVE']);
    Coach::create([
        'sae_id' => $sae->id,
        'user_id' => $coachUser->id,
        'name' => 'Coach Formation',
        'email' => $coachUser->email,
        'status' => 'ACTIF',
    ]);

    $this->actingAs($coachUser);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('coach'));

    Livewire::test(CoachListCourses::class)->assertSuccessful()->assertSee('Parcours Idéation');
    Livewire::test(CoachViewCourse::class, ['record' => $course->getKey()])
        ->assertSuccessful()
        ->assertDontSee('Afficher Cours')
        ->assertSee('Module 1');
    Livewire::test(CoachPreviewCourseLesson::class, ['record' => $course->getKey(), 'lesson' => $lesson->slug])
        ->assertSuccessful()
        ->assertSee('Leçon Intro');
});

test('entrepreneur accesses courses for their project level and can complete lessons', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE LMS',
        'slug' => 'sae-lms-'.uniqid(),
    ]);

    $level1 = Level::create(['sae_id' => $sae->id, 'name' => 'Niveau 1', 'order' => 1]);
    $level2 = Level::create(['sae_id' => $sae->id, 'name' => 'Niveau 2', 'order' => 2]);

    $course1 = Course::create([
        'sae_id' => $sae->id,
        'level_id' => $level1->id,
        'title' => 'Cours Niveau 1',
        'is_published' => true,
    ]);
    $module1 = CourseModule::create([
        'sae_id' => $sae->id,
        'course_id' => $course1->id,
        'title' => 'Introduction',
        'is_published' => true,
    ]);
    $lesson1 = CourseLesson::create([
        'sae_id' => $sae->id,
        'course_module_id' => $module1->id,
        'title' => 'Leçon Vidéo YouTube',
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        'duration_in_seconds' => 185,
        'is_published' => true,
    ]);

    $course2 = Course::create([
        'sae_id' => $sae->id,
        'level_id' => $level2->id,
        'title' => 'Cours Niveau 2',
        'is_published' => true,
    ]);
    $module2 = CourseModule::create([
        'sae_id' => $sae->id,
        'course_id' => $course2->id,
        'title' => 'Avancé',
        'is_published' => true,
    ]);
    $lockedLesson = CourseLesson::create([
        'sae_id' => $sae->id,
        'course_module_id' => $module2->id,
        'title' => 'Leçon Verrouillée',
        'is_free' => false,
        'is_published' => true,
    ]);
    $freePreviewLesson = CourseLesson::create([
        'sae_id' => $sae->id,
        'course_module_id' => $module2->id,
        'title' => 'Aperçu Gratuit',
        'is_free' => true,
        'is_published' => true,
    ]);

    $entUser = User::factory()->create();
    $entUser->saes()->attach($sae->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);
    $entrepreneur = Entrepreneur::create([
        'user_id' => $entUser->id,
        'sae_id' => $sae->id,
        'first_name' => 'Koffi',
        'last_name' => 'Mensah',
        'email' => $entUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Startup Niveau 1',
        'status' => ProjectStatus::ACTIF,
        'current_level_id' => $level1->id,
    ]);

    expect($lesson1->getEmbedVideoUrl())->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ?rel=0')
        ->and($lesson1->getFormattedDuration())->toBe('3:05')
        ->and($lesson1->isAccessibleBy($entrepreneur))->toBeTrue()
        ->and($lockedLesson->isAccessibleBy($entrepreneur))->toBeFalse()
        ->and($freePreviewLesson->isAccessibleBy($entrepreneur))->toBeTrue();

    $this->actingAs($entUser);
    Filament::setTenant($sae);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));

    Livewire::test(CoursesPage::class)
        ->assertSuccessful()
        ->assertSee('Cours Niveau 1')
        ->assertSee('Cours Niveau 2');

    // Page dédiée de visualisation du cours (avec modules et leçons)
    Livewire::test(CourseShowPage::class, ['course' => $course1->slug])
        ->assertSuccessful()
        ->assertSee('Cours Niveau 1')
        ->assertSee('Introduction')
        ->assertSee('Leçon Vidéo YouTube');

    // Leçon verrouillée -> redirection vers Mes cours
    Livewire::test(LessonPlayerPage::class, ['lesson' => $lockedLesson->slug])
        ->assertRedirect(CoursesPage::getUrl());

    // Leçon autorisée -> lecture et complétion
    Livewire::test(LessonPlayerPage::class, ['lesson' => $lesson1->slug])
        ->assertSuccessful()
        ->assertSee('Leçon Vidéo YouTube')
        ->call('toggleComplete');

    expect(
        LessonProgress::withoutGlobalScopes()
            ->where('user_id', $entUser->id)
            ->where('course_lesson_id', $lesson1->id)
            ->whereNotNull('completed_at')
            ->exists()
    )->toBeTrue();
});
