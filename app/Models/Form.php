<?php

namespace App\Models;

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packstub\FormBuilder\Models\Form as BaseForm;

class Form extends BaseForm
{
    protected $attributes = [
        'is_active' => true,
        'store_submissions' => true,
        'type' => FormType::CANDIDATURE,
        'is_template' => true,
        'is_mandatory' => true,
    ];

    protected static function booted(): void
    {
        parent::booted();

        static::creating(function (self $form): void {
            if ($form->type === null) {
                $form->type = FormType::CANDIDATURE;
            }
        });
    }

    public function toPortable(): array
    {
        return array_merge(parent::toPortable(), [
            'type' => $this->type instanceof FormType ? $this->type->value : $this->type,
            'is_template' => (bool) $this->is_template,
            'is_mandatory' => (bool) $this->is_mandatory,
        ]);
    }

    public static function fromArray(array $definition): static
    {
        $form = parent::fromArray($definition);

        if (isset($definition['type'])) {
            $form->type = $definition['type'];
        }

        if (isset($definition['is_template'])) {
            $form->is_template = (bool) $definition['is_template'];
        }

        if (isset($definition['is_mandatory'])) {
            $form->is_mandatory = (bool) $definition['is_mandatory'];
        }

        return $form;
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'type' => FormType::class,
            'is_mandatory' => 'boolean',
            'is_template' => 'boolean',
            'is_standard' => 'boolean',
        ]);
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'application_form_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function targetLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'target_level_id');
    }

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'sae_id');
    }

    public function unlocks(): HasMany
    {
        return $this->hasMany(FormUnlock::class, 'form_id');
    }

    public function totalUnlocksCount(): int
    {
        return $this->unlocks()->count();
    }

    public function completedUnlocksCount(): int
    {
        return $this->unlocks()->where('status', FormUnlockStatus::SOUMIS)->count();
    }

    public function pendingUnlocksCount(): int
    {
        return $this->unlocks()->where('status', '!=', FormUnlockStatus::SOUMIS)->count();
    }

    public function completionRate(): float
    {
        $total = $this->totalUnlocksCount();

        if ($total === 0) {
            return 0.0;
        }

        return round(($this->completedUnlocksCount() / $total) * 100, 1);
    }
}
