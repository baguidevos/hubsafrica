<?php

namespace App\Models;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class CoachingSession extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'coach_id',
        'project_id',
        'entrepreneur_id',
        'cohort_id',
        'cohort_phase_id',
        'date',
        'duration',
        'type',
        'theme',
        'report',
        'objectives',
        'next_actions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CoachingSessionStatus::class,
            'type' => CoachingSessionType::class,
            'date' => 'datetime',
            'duration' => 'integer',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class, 'coach_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function cohortPhase(): BelongsTo
    {
        return $this->belongsTo(CohortPhase::class, 'cohort_phase_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(CoachingSessionAttendee::class, 'coaching_session_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderByDesc('created_at');
    }

    public function isCollective(): bool
    {
        return in_array($this->type, [CoachingSessionType::COLLECTIF, CoachingSessionType::ATELIER, CoachingSessionType::FORMATION], true);
    }
}
