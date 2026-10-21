<?php

namespace App\Models;

use App\Enums\CohortStatus;
use App\Enums\ProgramPhaseStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cohort extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'program_id',
        'application_form_id',
        'name',
        'number',
        'capacity',
        'start_date',
        'end_date',
        'objectives',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CohortStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'number' => 'integer',
            'capacity' => 'integer',
            'application_form_id' => 'integer',
        ];
    }

    public function getMaxParticipantsAttribute(): ?int
    {
        return $this->capacity;
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function applicationForm(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'application_form_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(CohortPhase::class, 'cohort_id')->orderBy('position');
    }

    public function evaluators(): HasMany
    {
        return $this->hasMany(ApplicationEvaluator::class, 'cohort_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'cohort_id');
    }

    public function entrepreneurInvitations(): HasMany
    {
        return $this->hasMany(EntrepreneurInvitation::class, 'cohort_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'cohort_id');
    }

    public function coachingSessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'cohort_id');
    }

    /**
     * Synchronise et instancie les phases du programme pour cette cohorte.
     * Calcule automatiquement les dates prévisionnelles si start_date est renseignée.
     * Méthode idempotente.
     */
    public function syncPhasesFromProgram(): void
    {
        $currentDate = $this->start_date ? $this->start_date->copy() : null;

        foreach ($this->program->phases as $programPhase) {
            $startsOn = $currentDate ? $currentDate->copy() : null;
            $endsOn = null;

            if ($currentDate && $programPhase->duration_weeks) {
                $endsOn = $currentDate->copy()->addWeeks($programPhase->duration_weeks)->subDay();
                $currentDate = $endsOn->copy()->addDay();
            }

            $this->phases()->firstOrCreate(
                ['program_phase_id' => $programPhase->id],
                [
                    'sae_id' => $this->sae_id,
                    'name' => $programPhase->name,
                    'position' => $programPhase->position,
                    'starts_on' => $startsOn,
                    'ends_on' => $endsOn,
                    'status' => ProgramPhaseStatus::A_VENIR,
                ]
            );
        }
    }
}
