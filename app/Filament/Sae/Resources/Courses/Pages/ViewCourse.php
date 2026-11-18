<?php

namespace App\Filament\Sae\Resources\Courses\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\Courses\CourseResource;
use Filament\Actions\EditAction;
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
        $status = $course->is_published ? 'Publié' : 'Brouillon';

        return "{$status} · Palier : {$levelName}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
