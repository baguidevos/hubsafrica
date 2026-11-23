<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * After saving, redirect to the view page.
     */
    protected function getRedirectUrl(): string
    {
        return ProjectResource::getUrl('view', ['record' => $this->record]);
    }
}
