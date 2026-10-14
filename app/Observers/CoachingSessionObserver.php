<?php

namespace App\Observers;

use App\Mail\CoachingSessionScheduledMail;
use App\Models\CoachingSession;
use Illuminate\Support\Facades\Mail;

class CoachingSessionObserver
{
    /**
     * Handle the CoachingSession "created" event.
     */
    public function created(CoachingSession $session): void
    {
        $this->notifyParticipants($session);
    }

    /**
     * Handle the CoachingSession "updated" event.
     */
    public function updated(CoachingSession $session): void
    {
        if ($session->wasChanged('date') && $session->date) {
            $this->notifyParticipants($session);
        }
    }

    protected function notifyParticipants(CoachingSession $session): void
    {
        // 1. Email au Coach
        $coachEmail = $session->coach?->email ?: $session->coach?->user?->email;
        if ($coachEmail) {
            Mail::to($coachEmail)->queue(new CoachingSessionScheduledMail($session, 'COACH'));
        }

        // 2. Email à l'Entrepreneur
        $entrepreneurEmail = $session->entrepreneur?->email
            ?: ($session->project?->entrepreneur?->email ?: $session->project?->entrepreneur?->user?->email);

        if ($entrepreneurEmail) {
            Mail::to($entrepreneurEmail)->queue(new CoachingSessionScheduledMail($session, 'ENTREPRENEUR'));
        }
    }
}
