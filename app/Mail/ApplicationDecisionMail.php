<?php

namespace App\Mail;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationDecisionMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application
    ) {}

    public function isWaitlist(): bool
    {
        return $this->application->status === ApplicationStatus::EN_ATTENTE;
    }

    public function envelope(): Envelope
    {
        $programName = $this->application->program?->name ?? 'Programme Hub Africa';
        $subject = $this->isWaitlist()
            ? "Mise en liste d'attente — {$programName}"
            : "Notification relative à votre candidature — {$programName}";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-decision',
            with: [
                'application' => $this->application,
                'program' => $this->application->program,
                'cohort' => $this->application->cohort,
                'sae' => $this->application->program?->sae,
                'project' => $this->application->project,
                'entrepreneur' => $this->application->entrepreneur,
                'isWaitlist' => $this->application->status === ApplicationStatus::EN_ATTENTE,
                'portalUrl' => url('/portal'),
            ],
        );
    }
}
