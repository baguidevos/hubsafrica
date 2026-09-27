<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Pages;

use App\Filament\Sae\Resources\Entrepreneurs\EntrepreneurResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntrepreneurs extends ListRecords
{
    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
