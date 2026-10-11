<?php

namespace App\Filament\Admin\Resources\Saes\Pages;

use App\Filament\Admin\Resources\Saes\SaeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSae extends EditRecord
{
    protected static string $resource = SaeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $owner = $this->record->owner;
        if ($owner) {
            $this->record->members()->syncWithoutDetaching([
                $owner->id => [
                    'role' => 'RESPONSABLE',
                    'status' => 'ACTIVE',
                ],
            ]);
        }
    }
}
