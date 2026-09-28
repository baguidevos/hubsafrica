<?php

namespace App\Models;

use App\Enums\CoachAssignmentStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachAssignment extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'coach_id',
        'project_id',
        'program_id',
        'cohort_id',
        'assigned_at',
        'ended_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CoachAssignmentStatus::class,
            'assigned_at' => 'datetime',
            'ended_at' => 'datetime',
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

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }
}
