<?php

namespace App\Mail;

use App\Models\Sae;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SaeManagerWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?Sae $sae = null
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->sae?->name ?? 'Hub Africa';

        return new Envelope(
            subject: "Votre accès Responsable SAE Hub Africa ({$saeName})",
        );
    }

    public function content(): Content
    {
        $saeUrl = $this->sae
            ? url('/sae/'.$this->sae->slug)
            : url('/sae');

        return new Content(
            view: 'emails.sae-manager-welcome',
            with: [
                'user' => $this->user,
                'sae' => $this->sae,
                'saeUrl' => $saeUrl,
            ],
        );
    }
}
