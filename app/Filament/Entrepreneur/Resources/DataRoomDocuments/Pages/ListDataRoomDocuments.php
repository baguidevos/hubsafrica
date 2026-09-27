<?php

namespace App\Filament\Entrepreneur\Resources\DataRoomDocuments\Pages;

use App\Filament\Entrepreneur\Resources\DataRoomDocuments\DataRoomDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDataRoomDocuments extends ListRecords
{
    protected static string $resource = DataRoomDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un document')
                ->slideOver(),
        ];
    }
}
