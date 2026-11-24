<?php

namespace App\Models;

use App\Enums\FormAttemptStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAttempt extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'form_id',
        'program_id',
        'cohort_id',
        'user_id',
        'entrepreneur_id',
        'status',
        'last_step_label',
        'filled_fields_count',
        'total_fields_count',
        'last_filled_field',
        'last_error_reason',
        'validation_errors',
        'source_url',
        'ip_address',
        'started_at',
        'last_activity_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FormAttemptStatus::class,
            'filled_fields_count' => 'integer',
            'total_fields_count' => 'integer',
            'validation_errors' => 'array',
            'started_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }
}
