<?php

namespace App\Mail;

use App\Models\EntrepreneurInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EntrepreneurDirectInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public EntrepreneurInvitation $invitation
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->invitation->sae?->name ?? 'Hub Africa';
        $cohortName = $this->invitation->cohort?->name;

        $subject = $cohortName
            ? "Invitation à rejoindre la cohorte {$cohortName} — {$saeName}"
            : "Invitation à rejoindre la plateforme Hub Africa — {$saeName}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.entrepreneur-invitation',
            with: [
                'invitation' => $this->invitation,
                'sae' => $this->invitation->sae,
                'cohort' => $this->invitation->cohort,
                'invitationUrl' => $this->invitation->getInvitationUrl(),
            ],
        );
    }
}
