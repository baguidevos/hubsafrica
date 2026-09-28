<?php

namespace App\Filament\Coach\Resources\DataRoomDocuments\Pages;

use App\Filament\Coach\Resources\DataRoomDocuments\DataRoomDocumentResource;
use Filament\Resources\Pages\ListRecords;

class ListDataRoomDocuments extends ListRecords
{
    protected static string $resource = DataRoomDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
