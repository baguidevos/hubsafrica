<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations\Pages;

use App\Filament\Sae\Resources\EntrepreneurInvitations\EntrepreneurInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntrepreneurInvitations extends ListRecords
{
    protected static string $resource = EntrepreneurInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle invitation')
                ->icon('heroicon-o-paper-airplane'),
        ];
    }
}
