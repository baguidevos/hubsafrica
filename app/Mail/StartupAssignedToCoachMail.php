<?php

namespace App\Mail;

use App\Models\CoachAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StartupAssignedToCoachMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CoachAssignment $assignment
    ) {}

    public function envelope(): Envelope
    {
        $projectName = $this->assignment->project?->name ?? 'Nouvelle startup';

        return new Envelope(
            subject: "Nouvelle startup attribuée à votre portefeuille : {$projectName}",
        );
    }

    public function content(): Content
    {
        $project = $this->assignment->project;
        $entrepreneur = $project?->entrepreneur;
        $sae = $this->assignment->sae ?? $project?->sae;
        $coachUrl = $sae
            ? url('/coach/'.$sae->slug.'/projects/'.$project?->id)
            : url('/coach');

        return new Content(
            view: 'emails.startup-assigned-to-coach',
            with: [
                'assignment' => $this->assignment,
                'coach' => $this->assignment->coach,
                'project' => $project,
                'entrepreneur' => $entrepreneur,
                'sae' => $sae,
                'coachUrl' => $coachUrl,
            ],
        );
    }
}
