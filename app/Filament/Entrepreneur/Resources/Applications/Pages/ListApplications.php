<?php

namespace App\Filament\Entrepreneur\Resources\Applications\Pages;

use App\Filament\Entrepreneur\Resources\Applications\ApplicationResource;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
