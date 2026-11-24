<?php

namespace App\Services;

use App\Enums\FormAttemptStatus;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\FormAttempt;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Str;
use Packstub\FormBuilder\Fields\Types\HiddenField;
use Packstub\FormBuilder\Models\Form as BaseForm;

class FormAttemptTracker
{
    /**
     * Analyse pure de la progression d'un utilisateur sur un formulaire (aucun accès base).
     *
     * @param  array<string, mixed>  $data
     * @return array{filled_count: int, total_count: int, last_filled_field: ?string, last_step_label: ?string}
     */
    public function analyzeProgress(BaseForm $form, array $data): array
    {
        $sections = $form->sections();
        $totalCount = 0;
        $filledCount = 0;
        $lastFilledField = null;
        $lastStepLabel = null;

        foreach ($sections as $sectionIndex => $section) {
            $stepTitle = $section->label ?: ('Étape '.($sectionIndex + 1));

            foreach ($section->fields as $field) {
                if (! $field->isInput() || $field->type instanceof HiddenField) {
                    continue;
                }

                $totalCount++;
                $value = $data[$field->key] ?? null;

                if ($this->isFilledValue($value)) {
                    $filledCount++;
                    $lastFilledField = $field->label ?: $field->key;
                    $lastStepLabel = $stepTitle;
                }
            }
        }

        return [
            'filled_count' => $filledCount,
            'total_count' => $totalCount,
            'last_filled_field' => $lastFilledField ? Str::limit($lastFilledField, 255) : null,
            'last_step_label' => $lastStepLabel ? Str::limit($lastStepLabel, 255) : null,
        ];
    }

    /**
     * Transforme les erreurs de validation Livewire/Packstub en libellés lisibles par un humain.
     *
     * @param  array<string, array<int, string>|string>  $rawErrors
     * @return array{summary: string, details: array<string, string>}
     */
    public function summarizeValidationErrors(array $rawErrors, BaseForm $form): array
    {
        $fieldsByKey = $form->inputFields();
        $details = [];
        $parts = [];

        foreach ($rawErrors as $key => $messages) {
            $cleanKey = str_starts_with($key, 'data.') ? substr($key, 5) : $key;
            $message = is_array($messages) ? (string) ($messages[0] ?? '') : (string) $messages;

            if ($message === '') {
                continue;
            }

            $field = $fieldsByKey->get($cleanKey);
            $label = $field?->label ?: $cleanKey;

            $details[$label] = $message;
            $parts[] = "{$label} : {$message}";
        }

        return [
            'summary' => Str::limit(implode(' | ', $parts), 1000),
            'details' => $details,
        ];
    }

    /**
     * Formule une explication lisible du motif de non-finalisation.
     */
    public function inferReason(FormAttempt $attempt): string
    {
        if (filled($attempt->last_error_reason)) {
            return (string) $attempt->last_error_reason;
        }

        if ($attempt->status === FormAttemptStatus::SOUMIS) {
            return 'Formulaire finalisé et soumis.';
        }

        if ($attempt->filled_fields_count === 0) {
            return 'Formulaire ouvert mais aucun champ saisi (abandon immédiat).';
        }

        $stepPart = $attempt->last_step_label ? " à l'étape « {$attempt->last_step_label} »" : '';
        $fieldPart = $attempt->last_filled_field ? " (dernier champ : {$attempt->last_filled_field})" : '';

        return "Abandon en cours de saisie{$stepPart} — {$attempt->filled_fields_count}/{$attempt->total_fields_count} champs remplis{$fieldPart}.";
    }

    /**
     * @param  array<string, mixed>  $initialValues
     */
    public function recordStart(BaseForm $form, ?User $user, array $initialValues = [], ?string $sourceUrl = null, ?string $ipAddress = null): ?FormAttempt
    {
        if (! $form->exists || ! $user) {
            return null;
        }

        [$saeId, $programId, $cohortId] = $this->resolveScope($form, $initialValues, $sourceUrl);
        $entrepreneur = Entrepreneur::withoutGlobalScopes()->where('user_id', $user->id)->first();
        $progress = $this->analyzeProgress($form, $initialValues);

        $existing = FormAttempt::withoutGlobalScopes()
            ->where('form_id', $form->getKey())
            ->where('user_id', $user->id)
            ->where('status', '!=', FormAttemptStatus::SOUMIS->value)
            ->latest('id')
            ->first();

        if ($existing) {
            $existing->update([
                'sae_id' => $existing->sae_id ?? $saeId,
                'program_id' => $existing->program_id ?? $programId,
                'cohort_id' => $existing->cohort_id ?? $cohortId,
                'entrepreneur_id' => $existing->entrepreneur_id ?? $entrepreneur?->id,
                'total_fields_count' => $progress['total_count'],
                'source_url' => $sourceUrl ?: $existing->source_url,
                'ip_address' => $ipAddress ?: $existing->ip_address,
                'last_activity_at' => now(),
            ]);

            return $existing;
        }

        return FormAttempt::withoutGlobalScopes()->create([
            'sae_id' => $saeId,
            'form_id' => $form->getKey(),
            'program_id' => $programId,
            'cohort_id' => $cohortId,
            'user_id' => $user->id,
            'entrepreneur_id' => $entrepreneur?->id,
            'status' => FormAttemptStatus::EN_COURS,
            'last_step_label' => $progress['last_step_label'],
            'filled_fields_count' => $progress['filled_count'],
            'total_fields_count' => $progress['total_count'],
            'last_filled_field' => $progress['last_filled_field'],
            'source_url' => $sourceUrl,
            'ip_address' => $ipAddress,
            'started_at' => now(),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordProgress(int $attemptId, BaseForm $form, array $data): void
    {
        $attempt = FormAttempt::withoutGlobalScopes()->find($attemptId);

        if (! $attempt || $attempt->status === FormAttemptStatus::SOUMIS) {
            return;
        }

        $progress = $this->analyzeProgress($form, $data);

        $attempt->update([
            'last_step_label' => $progress['last_step_label'] ?? $attempt->last_step_label,
            'filled_fields_count' => $progress['filled_count'],
            'total_fields_count' => $progress['total_count'],
            'last_filled_field' => $progress['last_filled_field'] ?? $attempt->last_filled_field,
            'last_activity_at' => now(),
        ]);
    }

    /**
     * @param  array<string, array<int, string>|string>  $rawErrors
     * @param  array<string, mixed>  $data
     */
    public function recordValidationFailure(int $attemptId, BaseForm $form, array $rawErrors, array $data = []): void
    {
        $attempt = FormAttempt::withoutGlobalScopes()->find($attemptId);

        if (! $attempt || $attempt->status === FormAttemptStatus::SOUMIS) {
            return;
        }

        $formatted = $this->summarizeValidationErrors($rawErrors, $form);
        $progress = $this->analyzeProgress($form, $data);

        $attempt->update([
            'status' => FormAttemptStatus::BLOQUE_VALIDATION,
            'last_step_label' => $progress['last_step_label'] ?? $attempt->last_step_label,
            'filled_fields_count' => $progress['filled_count'],
            'total_fields_count' => $progress['total_count'],
            'last_filled_field' => $progress['last_filled_field'] ?? $attempt->last_filled_field,
            'last_error_reason' => $formatted['summary'] ?: 'Erreur de validation sur le formulaire.',
            'validation_errors' => $formatted['details'],
            'last_activity_at' => now(),
        ]);
    }

    public function recordClosedOrRuntimeError(int $attemptId, string $reason): void
    {
        $attempt = FormAttempt::withoutGlobalScopes()->find($attemptId);

        if (! $attempt || $attempt->status === FormAttemptStatus::SOUMIS) {
            return;
        }

        $attempt->update([
            'status' => FormAttemptStatus::BLOQUE_VALIDATION,
            'last_error_reason' => Str::limit($reason, 1000),
            'last_activity_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function recordSpamBlock(BaseForm $form, ?int $userId, string $reason, array $input = [], ?string $sourceUrl = null, ?string $ipAddress = null): void
    {
        if (! $form->exists || ! $userId) {
            return;
        }

        $humanReason = match ($reason) {
            'too_fast' => 'Soumission bloquée par l\'anti-spam : formulaire envoyé trop rapidement.',
            'honeypot' => 'Soumission bloquée par l\'anti-spam (champ piège rempli).',
            'token' => 'Soumission bloquée : jeton de session expiré ou déjà utilisé.',
            default => "Soumission bloquée par la protection anti-spam ({$reason}).",
        };

        $attempt = FormAttempt::withoutGlobalScopes()
            ->where('form_id', $form->getKey())
            ->where('user_id', $userId)
            ->where('status', '!=', FormAttemptStatus::SOUMIS->value)
            ->latest('id')
            ->first();

        if (! $attempt) {
            $user = User::find($userId);
            $attempt = $this->recordStart($form, $user, $input, $sourceUrl, $ipAddress);
        }

        $attempt?->update([
            'status' => FormAttemptStatus::BLOQUE_SPAM,
            'last_error_reason' => $humanReason,
            'last_activity_at' => now(),
        ]);
    }

    public function markSubmitted(BaseForm $form, ?int $userId): void
    {
        if (! $form->exists || ! $userId) {
            return;
        }

        FormAttempt::withoutGlobalScopes()
            ->where('form_id', $form->getKey())
            ->where('user_id', $userId)
            ->where('status', '!=', FormAttemptStatus::SOUMIS->value)
            ->update([
                'status' => FormAttemptStatus::SOUMIS,
                'last_error_reason' => null,
                'validation_errors' => null,
                'submitted_at' => now(),
                'last_activity_at' => now(),
            ]);
    }

    /**
     * @return list<string>
     */
    public function exportHeaders(): array
    {
        return [
            'Dernière activité',
            'Date d\'ouverture',
            'Statut',
            'Nom',
            'Email',
            'Téléphone',
            'Formulaire',
            'Cohorte',
            'Progression',
            'Dernière étape atteinte',
            'Dernier champ rempli',
            'Raison / Blocage',
        ];
    }

    /**
     * @return list<string>
     */
    public function formatExportRow(FormAttempt $attempt): array
    {
        $name = $attempt->entrepreneur?->full_name ?: ($attempt->user?->name ?? '—');
        $email = $attempt->entrepreneur?->email ?: ($attempt->user?->email ?? '—');
        $phone = $attempt->entrepreneur?->phone ?: ($attempt->user?->phone ?? '—');

        return [
            $attempt->last_activity_at?->format('d/m/Y H:i') ?? '—',
            $attempt->started_at?->format('d/m/Y H:i') ?? '—',
            $attempt->status?->getLabel() ?? '—',
            $name,
            $email,
            $phone,
            $attempt->form?->name ?? "Formulaire #{$attempt->form_id}",
            $attempt->cohort?->name ?? '—',
            "{$attempt->filled_fields_count}/{$attempt->total_fields_count} champs",
            $attempt->last_step_label ?? '—',
            $attempt->last_filled_field ?? '—',
            $this->inferReason($attempt),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     * @return array{0: ?int, 1: ?int, 2: ?int}
     */
    private function resolveScope(BaseForm $form, array $values, ?string $sourceUrl): array
    {
        $programId = isset($values['program_id']) && is_numeric($values['program_id'])
            ? (int) $values['program_id']
            : ($form->getAttribute('program_id') ? (int) $form->getAttribute('program_id') : null);

        $cohortId = isset($values['cohort_id']) && is_numeric($values['cohort_id'])
            ? (int) $values['cohort_id']
            : null;

        if (! $programId && $sourceUrl && preg_match('#/programmes/(\d+)#', $sourceUrl, $matches)) {
            $programId = (int) $matches[1];
        }

        if (! $cohortId && $sourceUrl) {
            $query = [];
            parse_str((string) parse_url($sourceUrl, PHP_URL_QUERY), $query);
            if (! empty($query['cohort']) && is_numeric($query['cohort'])) {
                $cohortId = (int) $query['cohort'];
            }
        }

        $saeId = $form->getAttribute('sae_id') ? (int) $form->getAttribute('sae_id') : null;

        if (! $saeId && $cohortId) {
            $saeId = Cohort::withoutGlobalScopes()->whereKey($cohortId)->value('sae_id');
        }

        if (! $saeId && $programId) {
            $saeId = Program::withoutGlobalScopes()->whereKey($programId)->value('sae_id');
        }

        return [$saeId ? (int) $saeId : null, $programId, $cohortId];
    }

    private function isFilledValue(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === []) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }
}
