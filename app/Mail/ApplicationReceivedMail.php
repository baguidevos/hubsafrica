<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application
    ) {}

    public function envelope(): Envelope
    {
        $programName = $this->application->program?->name ?? 'Programme Hub Africa';

        return new Envelope(
            subject: "Candidature enregistrée avec succès — {$programName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-received',
            with: [
                'application' => $this->application,
                'program' => $this->application->program,
                'cohort' => $this->application->cohort,
                'sae' => $this->application->program?->sae,
                'project' => $this->application->project,
                'entrepreneur' => $this->application->entrepreneur,
                'portalUrl' => url('/portal'),
            ],
        );
    }
}
