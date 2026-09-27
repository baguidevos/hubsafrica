<?php

namespace App\Filament\Sae\Resources\Coaches\Pages;

use App\Filament\Sae\Resources\Coaches\CoachResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoaches extends ListRecords
{
    protected static string $resource = CoachResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
