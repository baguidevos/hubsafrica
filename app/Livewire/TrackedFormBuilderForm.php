<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\FormAttemptTracker;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Packstub\FormBuilder\Livewire\FormBuilderForm;
use Packstub\FormBuilder\Models\Form as FormModel;

class TrackedFormBuilderForm extends FormBuilderForm
{
    #[Locked]
    public ?int $attemptId = null;

    public function mount(FormModel|array|string|int $form, array $values = [], bool $preview = false): void
    {
        parent::mount($form, $values, $preview);

        if ($this->preview || $this->formId === null) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $attempt = app(FormAttemptTracker::class)->recordStart(
            form: $this->getFormModel(),
            user: $user,
            initialValues: is_array($this->data) ? $this->data : $values,
            sourceUrl: request()->fullUrl(),
            ipAddress: request()->ip(),
        );

        $this->attemptId = $attempt?->id;
    }

    public function updatedData(): void
    {
        if ($this->preview || $this->attemptId === null) {
            return;
        }

        app(FormAttemptTracker::class)->recordProgress(
            attemptId: $this->attemptId,
            form: $this->getFormModel(),
            data: is_array($this->data) ? $this->data : [],
        );
    }

    public function submit(?string $captcha = null): void
    {
        $tracker = app(FormAttemptTracker::class);

        try {
            parent::submit($captcha);
        } catch (ValidationException $e) {
            if (! $this->preview && $this->attemptId !== null) {
                $tracker->recordValidationFailure(
                    attemptId: $this->attemptId,
                    form: $this->getFormModel(),
                    rawErrors: $e->errors(),
                    data: is_array($this->data) ? $this->data : [],
                );
            }

            throw $e;
        }

        if ($this->preview || $this->attemptId === null) {
            return;
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            $tracker->recordValidationFailure(
                attemptId: $this->attemptId,
                form: $this->getFormModel(),
                rawErrors: $this->getErrorBag()->toArray(),
                data: is_array($this->data) ? $this->data : [],
            );

            return;
        }

        if ($this->error !== null) {
            $tracker->recordClosedOrRuntimeError($this->attemptId, $this->error);
        }
    }
}
