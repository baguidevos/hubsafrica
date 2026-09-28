<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoachInvitation extends EditRecord
{
    protected static string $resource = CoachInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
