<?php

namespace App\Listeners;

use App\Services\FormAttemptTracker;
use Illuminate\Support\Facades\Auth;
use Packstub\FormBuilder\Events\SpamDetected;
use Packstub\FormBuilder\Events\SubmissionReceived;

class TrackFormAttemptEvents
{
    public function __construct(
        private readonly FormAttemptTracker $tracker,
    ) {}

    public function handle(SubmissionReceived|SpamDetected $event): void
    {
        if ($event instanceof SubmissionReceived) {
            $userId = $event->context->userId ?? $event->submission->user_id ?? Auth::id();
            $this->tracker->markSubmitted($event->form, $userId ? (int) $userId : null);

            return;
        }

        $userId = $event->context->userId ?? Auth::id();
        $this->tracker->recordSpamBlock(
            form: $event->form,
            userId: $userId ? (int) $userId : null,
            reason: $event->reason,
            input: $event->input,
            sourceUrl: $event->context->sourceUrl,
            ipAddress: $event->context->ip,
        );
    }
}
