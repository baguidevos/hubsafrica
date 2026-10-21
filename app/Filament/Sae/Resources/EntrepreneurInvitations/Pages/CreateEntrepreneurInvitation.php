<?php

namespace App\Filament\Sae\Resources\EntrepreneurInvitations\Pages;

use App\Filament\Sae\Resources\EntrepreneurInvitations\EntrepreneurInvitationResource;
use App\Mail\EntrepreneurDirectInvitationMail;
use App\Models\EntrepreneurInvitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateEntrepreneurInvitation extends CreateRecord
{
    protected static string $resource = EntrepreneurInvitationResource::class;

    protected function afterCreate(): void
    {
        /** @var EntrepreneurInvitation $record */
        $record = $this->getRecord();

        if ($record->email) {
            Mail::to($record->email)
                ->queue(new EntrepreneurDirectInvitationMail($record));
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        /** @var EntrepreneurInvitation $record */
        $record = $this->getRecord();

        return $record->makeInvitationNotification();
    }
}
