<?php

namespace App\Observers;

use App\Enums\ApplicationStatus;
use App\Mail\ApplicationAdmittedMail;
use App\Mail\ApplicationDecisionMail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class ApplicationObserver
{
    /**
     * Handle the Application "updated" event.
     */
    public function updated(Application $application): void
    {
        if (! $application->wasChanged('status')) {
            return;
        }

        $email = $application->entrepreneur?->email
            ?: $application->entrepreneur?->user?->email;

        if (! $email) {
            return;
        }

        if ($application->status === ApplicationStatus::ACCEPTEE) {
            Mail::to($email)->queue(new ApplicationAdmittedMail($application));
        } elseif (in_array($application->status, [ApplicationStatus::REFUSEE, ApplicationStatus::EN_ATTENTE], true)) {
            Mail::to($email)->queue(new ApplicationDecisionMail($application));
        }
    }
}
