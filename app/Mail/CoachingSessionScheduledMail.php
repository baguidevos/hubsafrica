<?php

namespace App\Mail;

use App\Models\CoachingSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachingSessionScheduledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachingSession $session,
        public string $recipientRole = 'ENTREPRENEUR' // ENTREPRENEUR ou COACH
    ) {}

    public function envelope(): Envelope
    {
        $dateFormatted = $this->session->date
            ? $this->session->date->format('d/m/Y à H:i')
            : 'Prochainement';
        $theme = $this->session->theme ?: 'Séance de coaching';

        return new Envelope(
            subject: "Convocation : {$theme} ({$dateFormatted})",
        );
    }

    public function content(): Content
    {
        $project = $this->session->project;
        $sae = $this->session->sae;
        $targetUrl = $this->recipientRole === 'COACH'
            ? ($sae ? url('/coach/'.$sae->slug.'/coaching-sessions') : url('/coach'))
            : url('/portal');

        return new Content(
            view: 'emails.coaching-session-scheduled',
            with: [
                'session' => $this->session,
                'coach' => $this->session->coach,
                'project' => $project,
                'entrepreneur' => $this->session->entrepreneur ?: $project?->entrepreneur,
                'sae' => $sae,
                'recipientRole' => $this->recipientRole,
                'targetUrl' => $targetUrl,
            ],
        );
    }
}
