<?php

namespace App\Models;

use App\Enums\FormType;
use App\Enums\FormUnlockStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Packstub\FormBuilder\Models\Form as BaseForm;

class Form extends BaseForm
{
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
