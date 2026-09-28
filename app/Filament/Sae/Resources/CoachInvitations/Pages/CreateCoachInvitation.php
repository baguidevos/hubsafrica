<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use App\Models\CoachInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachInvitation extends CreateRecord
{
    protected static string $resource = CoachInvitationResource::class;

    protected function getCreatedNotification(): ?Notification
    {
        /** @var CoachInvitation $record */
        $record = $this->getRecord();

        return $record->makeInvitationNotification();
    }
}
