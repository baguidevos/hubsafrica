<?php

namespace App\Filament\Sae\Resources\Indicators\Pages;

use App\Filament\Sae\Resources\Indicators\IndicatorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIndicator extends EditRecord
{
    protected static string $resource = IndicatorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
