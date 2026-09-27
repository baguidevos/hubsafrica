<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'name',
        'description',
        'objectives',
        'target',
        'capacity',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'level_id',
        'eligibility',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ProgramStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start' => 'date',
            'registration_end' => 'date',
            'capacity' => 'integer',
        ];
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'program_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'program_id');
    }

    public function deliverableDefinitions(): HasMany
    {
        return $this->hasMany(DeliverableDefinition::class, 'program_id');
    }
}
