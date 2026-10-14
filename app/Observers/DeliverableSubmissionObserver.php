<?php

namespace App\Observers;

use App\Enums\DeliverableSubmissionStatus;
use App\Mail\DeliverableStatusMail;
use App\Models\DeliverableSubmission;
use Illuminate\Support\Facades\Mail;

class DeliverableSubmissionObserver
{
    /**
     * Handle the DeliverableSubmission "updated" event.
     */
    public function updated(DeliverableSubmission $submission): void
    {
        if (! $submission->wasChanged('status')) {
            return;
        }

        if (! in_array($submission->status, [
            DeliverableSubmissionStatus::VALIDE,
            DeliverableSubmissionStatus::VALIDEE,
            DeliverableSubmissionStatus::CORRECTION_DEMANDEE,
            DeliverableSubmissionStatus::REJETEE,
            DeliverableSubmissionStatus::NON_VALIDE,
        ], true)) {
            return;
        }

        $email = $submission->entrepreneur?->email
            ?: ($submission->project?->entrepreneur?->email ?: $submission->project?->entrepreneur?->user?->email);

        if ($email) {
            Mail::to($email)->queue(new DeliverableStatusMail($submission));
        }
    }
}
