<?php

namespace App\Filament\Sae\Resources\Partners\Pages;

use App\Filament\Sae\Resources\Partners\PartnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartners extends ListRecords
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau partenaire')
                ->icon('heroicon-o-plus'),
        ];
    }
}
