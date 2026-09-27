<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingSession extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'coach_id',
        'project_id',
        'entrepreneur_id',
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
            'status' => \App\Enums\CoachingSessionStatus::class,
            'type' => \App\Enums\CoachingSessionType::class,
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
}
