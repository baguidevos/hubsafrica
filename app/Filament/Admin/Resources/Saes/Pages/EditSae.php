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
}


