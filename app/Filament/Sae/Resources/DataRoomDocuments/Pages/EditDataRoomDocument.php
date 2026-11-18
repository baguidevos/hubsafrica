<?php

namespace App\Filament\Sae\Resources\DataRoomDocuments\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\DataRoomDocuments\DataRoomDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDataRoomDocument extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = DataRoomDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
