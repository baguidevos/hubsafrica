<?php

namespace App\Filament\Sae\Resources\CourseModules\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\CourseModules\CourseModuleResource;
use App\Filament\Sae\Resources\Courses\CourseResource;
use App\Filament\Sae\Resources\Courses\Pages\EditCourse;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourseModule extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = CourseModuleResource::class;

    public function getBreadcrumbs(): array
    {
        $module = $this->getRecord();
        $course = $module->course;

        $breadcrumbs = [
            CourseResource::getUrl('index') => 'Cours & Parcours',
        ];

        if ($course) {
            $breadcrumbs[EditCourse::getUrl(['record' => $course])] = $course->title;
        }

        $breadcrumbs[] = $module->title;

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->getRecord()->course) {
            $actions[] = Action::make('back_to_course')
                ->label('Retour au cours')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(EditCourse::getUrl(['record' => $this->getRecord()->course]));
        }

        $actions[] = DeleteAction::make();

        return $actions;
    }
}
