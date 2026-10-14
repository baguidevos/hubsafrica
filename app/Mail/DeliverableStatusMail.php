<?php

namespace App\Mail;

use App\Enums\DeliverableSubmissionStatus;
use App\Models\DeliverableSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeliverableStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public DeliverableSubmission $submission
    ) {}

    public function isValidated(): bool
    {
        return in_array($this->submission->status, [DeliverableSubmissionStatus::VALIDE, DeliverableSubmissionStatus::VALIDEE], true);
    }

    public function envelope(): Envelope
    {
        $deliverableTitle = $this->submission->definition?->name ?? $this->submission->definition?->title ?? 'Livrable';
        $subject = $this->isValidated()
            ? "Livrable validé avec succès : {$deliverableTitle} ✅"
            : "Mise à jour requise sur votre livrable : {$deliverableTitle} 📝";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $project = $this->submission->project;
        $sae = $this->submission->sae ?? $project?->sae;
        $isValidated = in_array($this->submission->status, [DeliverableSubmissionStatus::VALIDE, DeliverableSubmissionStatus::VALIDEE], true);

        return new Content(
            view: 'emails.deliverable-status',
            with: [
                'submission' => $this->submission,
                'definition' => $this->submission->definition,
                'project' => $project,
                'entrepreneur' => $this->submission->entrepreneur ?: $project?->entrepreneur,
                'sae' => $sae,
                'isValidated' => $isValidated,
                'portalUrl' => url('/portal'),
            ],
        );
    }
}
