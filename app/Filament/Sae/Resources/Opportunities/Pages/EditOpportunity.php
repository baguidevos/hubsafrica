<?php

namespace App\Filament\Sae\Resources\Opportunities\Pages;

use App\Filament\Concerns\HasDynamicRecordTitle;
use App\Filament\Sae\Resources\Opportunities\OpportunityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOpportunity extends EditRecord
{
    use HasDynamicRecordTitle;

    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
