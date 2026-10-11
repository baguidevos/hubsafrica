<?php

namespace App\Filament\Admin\Resources\SaeTypes\Pages;

use App\Filament\Admin\Resources\SaeTypes\SaeTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSaeTypes extends ListRecords
{
    protected static string $resource = SaeTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau type de SAE'),
        ];
    }
}
