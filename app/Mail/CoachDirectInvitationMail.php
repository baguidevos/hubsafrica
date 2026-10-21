<?php

namespace App\Mail;

use App\Models\CoachInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachDirectInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->invitation->sae?->name ?? 'Hubs Africa';

        return new Envelope(
            subject: "Invitation à rejoindre l'équipe d'accompagnement de {$saeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.coach-direct-invitation',
            with: [
                'invitation' => $this->invitation,
                'sae' => $this->invitation->sae,
                'invitationUrl' => $this->invitation->getInvitationUrl(),
            ],
        );
    }
}
