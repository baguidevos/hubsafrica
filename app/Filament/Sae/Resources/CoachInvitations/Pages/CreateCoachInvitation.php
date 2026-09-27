<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachInvitation extends CreateRecord
{
    protected static string $resource = CoachInvitationResource::class;

    protected function getCreatedNotification(): ?\Filament\Notifications\Notification
    {
        /** @var \App\Models\CoachInvitation $record */
        $record = $this->getRecord();

        return $record->makeInvitationNotification();
    }
}


