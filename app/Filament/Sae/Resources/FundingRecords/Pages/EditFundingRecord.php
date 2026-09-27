<?php

namespace App\Filament\Sae\Resources\FundingRecords\Pages;

use App\Filament\Sae\Resources\FundingRecords\FundingRecordResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFundingRecord extends EditRecord
{
    protected static string $resource = FundingRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}


