<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations\Pages;

use App\Filament\Sae\Resources\EntrepreneurInvitations\EntrepreneurInvitationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEntrepreneurInvitation extends EditRecord
{
    protected static string $resource = EntrepreneurInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
