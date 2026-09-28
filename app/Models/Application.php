<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Application extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'program_id',
        'cohort_id',
        'form_id',
        'answers',
        'entrepreneur_id',
        'project_id',
        'status',
        'score',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'decision',
        'comment',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'score' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'answers' => 'array',
            'form_id' => 'integer',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(ApplicationEvaluation::class, 'application_id');
    }

    public function evaluators(): HasMany
    {
        return $this->hasMany(ApplicationEvaluator::class, 'application_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Recalcule la note globale moyenne de la candidature à partir des évaluations soumises du jury.
     */
    public function recalculateAggregatedScore(): ?int
    {
        $averageScore = $this->evaluations()
            ->whereNotNull('total_score')
            ->avg('total_score');

        if ($averageScore !== null) {
            $rounded = (int) round($averageScore);
            $this->update(['score' => $rounded]);

            return $rounded;
        }

        return null;
    }
}
