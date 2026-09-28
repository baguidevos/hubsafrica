<?php

namespace App\Filament\Sae\Resources\CohortPhases\Pages;

use App\Filament\Sae\Resources\CohortPhases\CohortPhaseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCohortPhases extends ListRecords
{
    protected static string $resource = CohortPhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau jalon'),
        ];
    }
}
