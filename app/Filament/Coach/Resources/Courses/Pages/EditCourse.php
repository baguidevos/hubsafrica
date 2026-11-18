<?php

namespace App\Filament\Coach\Resources\Courses\Pages;

use App\Filament\Coach\Resources\Courses\CourseResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
