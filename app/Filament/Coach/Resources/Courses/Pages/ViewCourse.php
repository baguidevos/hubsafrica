<?php

namespace App\Filament\Coach\Resources\Courses\Pages;

use App\Filament\Coach\Resources\Courses\CourseResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Resources\Pages\ViewRecord;

class ViewCourse extends ViewRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = CourseResource::class;

    protected string $view = 'filament.sae.resources.courses.pages.view-course';

    public function getSubheading(): ?string
    {
        $course = $this->getRecord();
        $levelName = $course->level?->name ?? 'Tous paliers';

        return "Palier cible : {$levelName}";
    }
}
