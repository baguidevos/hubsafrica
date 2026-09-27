<?php

namespace App\Models;

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
            'status' => \App\Enums\CohortStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'number' => 'integer',
            'capacity' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'cohort_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'cohort_id');
    }
}
