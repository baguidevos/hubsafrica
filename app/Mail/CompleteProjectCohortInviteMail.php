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
        public ?Cohort $cohort = null,
        public ?Sae $sae = null,
        public ?string $customMessage = null,
        public ?string $customSubject = null,
        public bool $isCohortEnrollment = true,
    ) {}

    public function envelope(): Envelope
    {
        $saeName = $this->sae?->name ?? $this->entrepreneur->sae?->name ?? 'Hubs Africa';

        if (filled($this->customSubject)) {
            return new Envelope(subject: $this->customSubject);
        }

        $defaultSubject = $this->cohort
            ? "[Action requise] Complétez votre fiche projet pour la cohorte {$this->cohort->name} — {$saeName}"
            : "[Action requise] Complétez votre fiche projet sur Hubs Africa — {$saeName}";

        return new Envelope(subject: $defaultSubject);
    }

    public function content(): Content
    {
        $sae = $this->sae ?? $this->entrepreneur->sae;
        $latestProject = $sae
            ? $this->entrepreneur->projects()
                ->withoutGlobalScopes()
                ->where('sae_id', $sae->id)
                ->latest('id')
                ->first()
            : null;

        try {
            if ($sae && $latestProject) {
                $projectUrl = ProjectResource::getUrl('edit', [
                    'tenant' => $sae,
                    'record' => $latestProject->id,
                ], panel: 'entrepreneur');
            } elseif ($sae) {
                $projectUrl = ProjectResource::getUrl('create', ['tenant' => $sae], panel: 'entrepreneur');
            } else {
                $projectUrl = url('/portal');
            }
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
                'isCohortEnrollment' => $this->isCohortEnrollment,
                'projectUrl' => $projectUrl,
            ],
        );
    }
}
