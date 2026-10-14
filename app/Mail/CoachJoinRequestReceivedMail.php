<?php

namespace App\Mail;

use App\Models\CoachJoinRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachJoinRequestReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachJoinRequest $joinRequest
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->joinRequest->sae?->name ?? 'votre structure';

        return new Envelope(
            subject: "Nouvelle candidature de Coach pour {$saeName} ({$this->joinRequest->name})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.coach-join-request-received',
            with: [
                'joinRequest' => $this->joinRequest,
                'sae' => $this->joinRequest->sae,
            ],
        );
    }
}
