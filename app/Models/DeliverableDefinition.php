<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliverableDefinition extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'program_id',
        'level_id',
        'name',
        'description',
        'instructions',
        'is_required',
        'max_score',
        'passing_score',
        'max_attempts',
        'deadline',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'max_score' => 'integer',
            'passing_score' => 'integer',
            'max_attempts' => 'integer',
            'deadline' => 'datetime',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(DeliverableSubmission::class, 'definition_id');
    }
}
