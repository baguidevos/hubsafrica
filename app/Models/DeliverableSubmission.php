<?php

namespace App\Models;

use App\Enums\DeliverableSubmissionStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class DeliverableSubmission extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'definition_id',
        'cohort_phase_id',
        'project_id',
        'entrepreneur_id',
        'submitted_by_id',
        'version',
        'file_path',
        'file_name',
        'comment',
        'status',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => DeliverableSubmissionStatus::class,
            'version' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(DeliverableDefinition::class, 'definition_id');
    }

    public function cohortPhase(): BelongsTo
    {
        return $this->belongsTo(CohortPhase::class, 'cohort_phase_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'submission_id')->latest('evaluated_at');
    }

    public function latestEvaluation()
    {
        return $this->hasOne(Evaluation::class, 'submission_id')->latestOfMany('evaluated_at');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable')->orderByDesc('version');
    }

    public function latestDocument(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable')->latestOfMany('version');
    }
}
