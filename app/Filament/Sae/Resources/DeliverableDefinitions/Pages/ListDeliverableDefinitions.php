<?php

namespace App\Filament\Sae\Resources\DeliverableDefinitions\Pages;

use App\Filament\Sae\Resources\DeliverableDefinitions\DeliverableDefinitionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliverableDefinitions extends ListRecords
{
    protected static string $resource = DeliverableDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
