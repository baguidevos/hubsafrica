<?php

namespace App\Filament\Sae\Resources\Opportunities\Pages;

use App\Filament\Sae\Resources\Opportunities\OpportunityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOpportunities extends ListRecords
{
    protected static string $resource = OpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Publier une opportunité')
                ->icon('heroicon-o-plus')
                ->slideOver(),
        ];
    }
}
