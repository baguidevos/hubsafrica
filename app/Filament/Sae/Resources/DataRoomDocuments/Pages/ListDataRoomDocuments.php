<?php

namespace App\Filament\Sae\Resources\DataRoomDocuments\Pages;

use App\Filament\Sae\Resources\DataRoomDocuments\DataRoomDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataRoomDocuments extends ListRecords
{
    protected static string $resource = DataRoomDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
