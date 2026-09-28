<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramPhase extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'program_id',
        'name',
        'description',
        'objectives',
        'passing_criteria',
        'position',
        'duration_weeks',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'duration_weeks' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function cohortPhases(): HasMany
    {
        return $this->hasMany(CohortPhase::class, 'program_phase_id');
    }

    public function deliverableDefinitions(): HasMany
    {
        return $this->hasMany(DeliverableDefinition::class, 'program_phase_id');
    }
}
