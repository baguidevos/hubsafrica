<?php

namespace App\Models;

use App\Enums\ProgramPhaseStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CohortPhase extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'cohort_id',
        'program_phase_id',
        'name',
        'position',
        'starts_on',
        'ends_on',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProgramPhaseStatus::class,
            'starts_on' => 'date',
            'ends_on' => 'date',
            'position' => 'integer',
        ];
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function programPhase(): BelongsTo
    {
        return $this->belongsTo(ProgramPhase::class, 'program_phase_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(DeliverableSubmission::class, 'cohort_phase_id');
    }

    public function coachingSessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'cohort_phase_id');
    }
}
