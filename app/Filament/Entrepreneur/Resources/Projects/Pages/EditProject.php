<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * After saving, redirect to the view page.
     */
    protected function getRedirectUrl(): string
    {
        return ProjectResource::getUrl('view', ['record' => $this->record]);
    }
}
