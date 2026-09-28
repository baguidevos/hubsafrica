<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoachingSessionAttendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'coaching_session_id',
        'project_id',
        'attended',
        'feedback',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attended' => 'boolean',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(CoachingSession::class, 'coaching_session_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
