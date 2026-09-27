<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoachInvitations extends ListRecords
{
    protected static string $resource = CoachInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Inviter un coach')
                ->slideOver()
                ->successNotification(fn (\App\Models\CoachInvitation $record) => $record->makeInvitationNotification()),
        ];
    }
}
