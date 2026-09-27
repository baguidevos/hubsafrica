<?php

namespace App\Filament\Sae\Resources\DeliverableDefinitions\Pages;

use App\Filament\Sae\Resources\DeliverableDefinitions\DeliverableDefinitionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliverableDefinition extends EditRecord
{
    protected static string $resource = DeliverableDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}


