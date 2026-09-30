<?php

namespace App\Listeners;

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use App\Models\Entrepreneur;
use App\Models\FormUnlock;
use App\Models\TimelineEvent;
use App\Models\User;
use Filament\Notifications\Notification;
use Packstub\FormBuilder\Events\SubmissionReceived;

class HandleEvaluationSubmission
{
    public function handle(SubmissionReceived $event): void
    {
        $form = $event->form;

        // Les candidatures sont traitées par HandleCandidatureSubmission
        if ($form->type === FormType::CANDIDATURE) {
            return;
        }

        $submission = $event->submission;

        // Retrieve user ID from submission context or current session
        $userId = $submission->user_id ?? auth()->id();

        if (! $userId) {
            return;
        }

        $entrepreneur = Entrepreneur::where('user_id', $userId)->first();

        if (! $entrepreneur) {
            return;
        }

        // Find or create the unlock record
        $unlock = FormUnlock::where('form_id', $form->getKey())
            ->where('entrepreneur_id', $entrepreneur->id)
            ->first();

        if ($unlock) {
            $unlock->update([
                'status' => FormUnlockStatus::SOUMIS,
                'completed_at' => now(),
                'submission_id' => $submission->getKey(),
            ]);
        } else {
            $unlock = FormUnlock::create([
                'sae_id' => $form->getAttribute('sae_id') ?? $entrepreneur->sae_id,
                'form_id' => $form->getKey(),
                'entrepreneur_id' => $entrepreneur->id,
                'status' => FormUnlockStatus::SOUMIS,
                'completed_at' => now(),
                'submission_id' => $submission->getKey(),
            ]);
        }

        // Create timeline event
        TimelineEvent::create([
            'sae_id' => $form->getAttribute('sae_id') ?? $entrepreneur->sae_id,
            'entrepreneur_id' => $entrepreneur->id,
            'title' => 'Questionnaire complété : '.$form->name,
            'description' => "L'entrepreneur {$entrepreneur->full_name} a complété avec succès le formulaire '{$form->name}'.",
            'type' => 'EVALUATION',
            'event_date' => now(),
        ]);

        // In-app notification to the entrepreneur
        if ($entrepreneurUser = $entrepreneur->user) {
            Notification::make()
                ->title('Questionnaire enregistré avec succès')
                ->body("Votre réponse au formulaire '{$form->name}' a bien été transmise.")
                ->success()
                ->sendToDatabase($entrepreneurUser);
        }

        // In-app notification to SAE managers
        $saeId = $form->getAttribute('sae_id') ?? $entrepreneur->sae_id;
        $saeUsers = User::whereHas('saes', fn ($q) => $q->where('saes.id', $saeId))->get();

        foreach ($saeUsers as $saeUser) {
            // Avoid notifying the submitter themselves
            if ($saeUser->id === (int) $userId) {
                continue;
            }

            Notification::make()
                ->title('Nouvelle réponse reçue')
                ->body("{$entrepreneur->full_name} a soumis ses réponses pour '{$form->name}'.")
                ->icon('heroicon-o-clipboard-document-check')
                ->info()
                ->sendToDatabase($saeUser);
        }
    }
}
