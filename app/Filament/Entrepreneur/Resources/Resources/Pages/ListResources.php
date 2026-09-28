<?php

namespace App\Filament\Entrepreneur\Resources\Resources\Pages;

use App\Filament\Entrepreneur\Resources\Resources\ResourceResource;
use Filament\Resources\Pages\ListRecords;

class ListResources extends ListRecords
{
    protected static string $resource = ResourceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
