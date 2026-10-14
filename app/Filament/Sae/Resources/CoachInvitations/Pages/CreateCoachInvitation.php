<?php

namespace App\Filament\Sae\Resources\CoachInvitations\Pages;

use App\Filament\Sae\Resources\CoachInvitations\CoachInvitationResource;
use App\Mail\CoachDirectInvitationMail;
use App\Models\CoachInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateCoachInvitation extends CreateRecord
{
    protected static string $resource = CoachInvitationResource::class;

    protected function afterCreate(): void
    {
        /** @var CoachInvitation $record */
        $record = $this->getRecord();

        if ($record->email) {
            Mail::to($record->email)
                ->queue(new CoachDirectInvitationMail($record));
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var CoachInvitation $record */
        $record = $this->getRecord();

        return $record->makeInvitationNotification();
    }
}
