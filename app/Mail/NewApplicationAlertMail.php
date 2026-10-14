<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewApplicationAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application
    ) {}

    public function envelope(): Envelope
    {
        $candidateName = $this->application->entrepreneur?->full_name ?? 'Nouveau candidat';
        $projectName = $this->application->project?->name ?? 'Projet';

        return new Envelope(
            subject: "Nouvelle candidature reçue : {$projectName} ({$candidateName})",
        );
    }

    public function content(): Content
    {
        $sae = $this->application->program?->sae;
        $reviewUrl = $sae
            ? url("/sae/{$sae->slug}/applications/{$this->application->id}")
            : url('/sae');

        return new Content(
            view: 'emails.new-application-alert',
            with: [
                'application' => $this->application,
                'program' => $this->application->program,
                'cohort' => $this->application->cohort,
                'sae' => $sae,
                'project' => $this->application->project,
                'entrepreneur' => $this->application->entrepreneur,
                'reviewUrl' => $reviewUrl,
            ],
        );
    }
}
