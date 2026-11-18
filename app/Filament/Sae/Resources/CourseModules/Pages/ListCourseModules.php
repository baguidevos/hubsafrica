<?php

namespace App\Filament\Sae\Resources\CourseModules\Pages;

use App\Filament\Sae\Resources\CourseModules\CourseModuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCourseModules extends ListRecords
{
    protected static string $resource = CourseModuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
