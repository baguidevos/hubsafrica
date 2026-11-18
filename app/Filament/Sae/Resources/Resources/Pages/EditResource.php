<?php

namespace App\Filament\Sae\Resources\Resources\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\Resources\ResourceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResource extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
