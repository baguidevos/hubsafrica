<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Pages;

use App\Filament\Sae\Resources\EntrepreneurInvitations\Pages\CreateEntrepreneurInvitation;
use App\Filament\Sae\Resources\Entrepreneurs\EntrepreneurResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEntrepreneurs extends ListRecords
{
    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invite')
                ->label('Inviter un entrepreneur')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->url(fn () => CreateEntrepreneurInvitation::getUrl()),
            CreateAction::make()
                ->label('Créer manuellement')
                ->slideOver(),
        ];
    }
}
