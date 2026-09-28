<?php

namespace App\Filament\Admin\Resources\Saes\Pages;

use App\Filament\Admin\Resources\Saes\SaeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaes extends ListRecords
{
    protected static string $resource = SaeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
