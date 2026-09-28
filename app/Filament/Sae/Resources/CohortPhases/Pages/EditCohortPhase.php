<?php

namespace App\Filament\Sae\Resources\CohortPhases\Pages;

use App\Filament\Sae\Resources\CohortPhases\CohortPhaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCohortPhase extends EditRecord
{
    protected static string $resource = CohortPhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
