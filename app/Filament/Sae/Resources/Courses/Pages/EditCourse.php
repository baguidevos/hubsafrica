<?php

namespace App\Filament\Sae\Resources\Courses\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\Courses\CourseResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->label('Prévisualiser le cours'),
            DeleteAction::make(),
        ];
    }
}
