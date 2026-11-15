<?php

namespace App\Filament\Sae\Resources\AccompanimentTools\Pages;

use App\Filament\Sae\Resources\AccompanimentTools\AccompanimentToolResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAccompanimentTool extends EditRecord
{
    protected static string $resource = AccompanimentToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
