<?php

namespace App\Mail;

use App\Models\Coach;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?Coach $coach = null,
        public ?Sae $sae = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Bienvenue dans le réseau des Mentors & Coachs Hubs Africa',
        );
    }

    public function content(): Content
    {
        $coachUrl = $this->sae
            ? url('/coach/'.$this->sae->slug)
            : url('/coach');

        return new Content(
            view: 'emails.coach-welcome',
            with: [
                'user' => $this->user,
                'coach' => $this->coach,
                'sae' => $this->sae,
                'coachUrl' => $coachUrl,
            ],
        );
    }
}
