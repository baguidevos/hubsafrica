<?php

namespace App\Models;

use App\Enums\FormUnlockStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Packstub\FormBuilder\Models\FormSubmission;

class FormUnlock extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'form_id',
        'entrepreneur_id',
        'unlocked_by_id',
        'status',
        'due_date',
        'completed_at',
        'submission_id',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'status' => FormUnlockStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function unlockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'unlocked_by_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class, 'submission_id');
    }

    public function isOverdue(): bool
    {
        return $this->status !== FormUnlockStatus::SOUMIS
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public function isCompleted(): bool
    {
        return $this->status === FormUnlockStatus::SOUMIS;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', '!=', FormUnlockStatus::SOUMIS);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', FormUnlockStatus::SOUMIS);
    }
}
