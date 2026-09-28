<?php

namespace App\Models;

use App\Enums\CoachStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'user_id',
        'name',
        'email',
        'phone',
        'specialties',
        'availability',
        'max_capacity',
        'is_external',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => CoachStatus::class,
            'specialties' => 'array',
            'is_external' => 'boolean',
            'max_capacity' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CoachAssignment::class, 'coach_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'coach_id');
    }

    public function activeAssignmentsCount(): int
    {
        return $this->assignments()->where('status', 'ACTIF')->count();
    }

    public function hasCapacity(): bool
    {
        return $this->activeAssignmentsCount() < $this->max_capacity;
    }
}
