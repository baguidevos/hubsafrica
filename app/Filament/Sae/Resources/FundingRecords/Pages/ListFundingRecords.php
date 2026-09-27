<?php

namespace App\Filament\Sae\Resources\FundingRecords\Pages;

use App\Filament\Sae\Resources\FundingRecords\FundingRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFundingRecords extends ListRecords
{
    protected static string $resource = FundingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
