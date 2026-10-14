<?php

namespace App\Mail;

use App\Models\CoachInvitation;
use App\Models\CoachJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachJoinRequestAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachJoinRequest $joinRequest,
        public CoachInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->joinRequest->sae?->name ?? 'Hub Africa';

        return new Envelope(
            subject: "Votre demande d'intégration chez {$saeName} a été acceptée !",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.coach-join-request-accepted',
            with: [
                'joinRequest' => $this->joinRequest,
                'invitation' => $this->invitation,
                'sae' => $this->joinRequest->sae,
                'invitationUrl' => $this->invitation->getInvitationUrl(),
            ],
        );
    }
}
