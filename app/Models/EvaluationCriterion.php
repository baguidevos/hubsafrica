<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCriterion extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'sae_id',
        'program_id',
        'name',
        'description',
        'weight',
        'max_score',
        'position',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'max_score' => 'integer',
            'position' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ApplicationEvaluationScore::class, 'criterion_id');
    }
}
