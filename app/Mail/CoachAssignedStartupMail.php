<?php

namespace App\Mail;

use App\Models\CoachAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CoachAssignedStartupMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachAssignment $assignment
    ) {}

    public function envelope(): Envelope
    {
        $coachName = $this->assignment->coach?->name ?? 'Votre mentor';

        return new Envelope(
            subject: "Votre coach référent a été assigné : {$coachName}",
        );
    }

    public function content(): Content
    {
        $project = $this->assignment->project;
        $entrepreneur = $project?->entrepreneur;
        $sae = $this->assignment->sae ?? $project?->sae;

        return new Content(
            view: 'emails.coach-assigned-startup',
            with: [
                'assignment' => $this->assignment,
                'coach' => $this->assignment->coach,
                'project' => $project,
                'entrepreneur' => $entrepreneur,
                'sae' => $sae,
                'portalUrl' => url('/portal'),
            ],
        );
    }
}
