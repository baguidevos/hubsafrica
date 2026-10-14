<?php

namespace App\Observers;

use App\Enums\CoachAssignmentStatus;
use App\Mail\CoachAssignedStartupMail;
use App\Mail\StartupAssignedToCoachMail;
use App\Models\CoachAssignment;
use Illuminate\Support\Facades\Mail;

class CoachAssignmentObserver
{
    /**
     * Handle the CoachAssignment "created" event.
     */
    public function created(CoachAssignment $assignment): void
    {
        $this->notifyParties($assignment);
    }

    /**
     * Handle the CoachAssignment "updated" event.
     */
    public function updated(CoachAssignment $assignment): void
    {
        if ($assignment->wasChanged('status') && $assignment->status === CoachAssignmentStatus::ACTIF) {
            $this->notifyParties($assignment);
        }
    }

    protected function notifyParties(CoachAssignment $assignment): void
    {
        if ($assignment->status !== CoachAssignmentStatus::ACTIF) {
            return;
        }

        // Notification de l'entrepreneur
        $entrepreneurEmail = $assignment->project?->entrepreneur?->email
            ?: $assignment->project?->entrepreneur?->user?->email;

        if ($entrepreneurEmail) {
            Mail::to($entrepreneurEmail)->queue(new CoachAssignedStartupMail($assignment));
        }

        // Notification du coach
        $coachEmail = $assignment->coach?->email
            ?: $assignment->coach?->user?->email;

        if ($coachEmail) {
            Mail::to($coachEmail)->queue(new StartupAssignedToCoachMail($assignment));
        }
    }
}
