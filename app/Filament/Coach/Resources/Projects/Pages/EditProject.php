<?php

namespace App\Filament\Coach\Resources\Projects\Pages;

use App\Filament\Coach\Resources\Projects\ProjectResource;
use App\Filament\Concerns\HasDynamicRecordTitle;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
