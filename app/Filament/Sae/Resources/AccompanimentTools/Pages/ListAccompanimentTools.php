<?php

namespace App\Filament\Sae\Resources\AccompanimentTools\Pages;

use App\Filament\Sae\Resources\AccompanimentTools\AccompanimentToolResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccompanimentTools extends ListRecords
{
    protected static string $resource = AccompanimentToolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
