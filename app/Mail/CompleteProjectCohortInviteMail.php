<?php

namespace App\Mail;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Sae;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompleteProjectCohortInviteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Entrepreneur $entrepreneur,
        public Cohort $cohort,
        public ?Sae $sae = null,
        public ?string $customMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->sae?->name ?? $this->entrepreneur->sae?->name ?? 'Hub Africa';

        return new Envelope(
            subject: "Complétez votre fiche projet pour la cohorte {$this->cohort->name} — {$saeName}",
        );
    }

    public function content(): Content
    {
        $sae = $this->sae ?? $this->entrepreneur->sae;

        try {
            $projectUrl = $sae
                ? ProjectResource::getUrl('create', ['tenant' => $sae], panel: 'entrepreneur')
                : url('/portal');
        } catch (\Throwable) {
            $projectUrl = url('/portal');
        }

        return new Content(
            view: 'emails.complete-project-cohort-invite',
            with: [
                'entrepreneur' => $this->entrepreneur,
                'cohort' => $this->cohort,
                'sae' => $sae,
                'customMessage' => $this->customMessage,
                'projectUrl' => $projectUrl,
            ],
        );
    }
}
