<?php

namespace App\Models;

use App\Enums\ApplicationEvaluationStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplicationEvaluation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'application_id',
        'evaluator_id',
        'comment',
        'total_score',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationEvaluationStatus::class,
            'total_score' => 'decimal:2',
            'submitted_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(ApplicationEvaluationScore::class, 'evaluation_id');
    }

    /**
     * Calcule le score global pondéré de cette évaluation (ramené sur 100).
     */
    public function calculateWeightedScore(): ?float
    {
        $scores = $this->scores()->with('criterion')->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $totalWeightedScore = 0.0;
        $totalWeight = 0;

        foreach ($scores as $item) {
            $criterion = $item->criterion;
            if (! $criterion || $criterion->max_score <= 0) {
                continue;
            }

            // Normalisation sur 100 pondérée
            $normalizedScore = ((float) $item->score / $criterion->max_score) * 100;
            $totalWeightedScore += $normalizedScore * $criterion->weight;
            $totalWeight += $criterion->weight;
        }

        if ($totalWeight === 0) {
            return null;
        }

        $finalScore = round($totalWeightedScore / $totalWeight, 2);
        $this->update(['total_score' => $finalScore]);

        // Mise à jour de l'application parente
        $this->application->recalculateAggregatedScore();

        return $finalScore;
    }
}
