<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationEvaluationScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluation_id',
        'criterion_id',
        'score',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
        ];
    }

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(ApplicationEvaluation::class, 'evaluation_id');
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'criterion_id');
    }
}
