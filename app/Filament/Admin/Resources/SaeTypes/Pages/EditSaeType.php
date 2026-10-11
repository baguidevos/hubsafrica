<?php

namespace App\Filament\Admin\Resources\SaeTypes\Pages;

use App\Filament\Admin\Resources\SaeTypes\SaeTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSaeType extends EditRecord
{
    protected static string $resource = SaeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
