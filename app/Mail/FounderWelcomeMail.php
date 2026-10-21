<?php

namespace App\Mail;

use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FounderWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?Entrepreneur $entrepreneur = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue sur Hubs Africa — Votre Espace Entrepreneur',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.founder-welcome',
            with: [
                'user' => $this->user,
                'entrepreneur' => $this->entrepreneur,
                'portalUrl' => url('/portal'),
            ],
        );
    }
}
