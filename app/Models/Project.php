<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'entrepreneur_id',
        'name',
        'description',
        'problem',
        'solution',
        'product_service',
        'value_proposition',
        'target',
        'market',
        'sector',
        'territory',
        'sdgs',
        'logo',
        'pitch',
        'team_size',
        'status',
        'current_level_id',
        'current_level_entered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\ProjectStatus::class,
            'sdgs' => 'array',
            'team_size' => 'integer',
            'current_level_entered_at' => 'datetime',
        ];
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function levelHistories(): HasMany
    {
        return $this->hasMany(ProjectLevelHistory::class, 'project_id')->orderBy('started_at');
    }

    public function updateLogs(): HasMany
    {
        return $this->hasMany(ProjectUpdateLog::class, 'project_id')->latest();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(CoachAssignment::class, 'project_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(DeliverableSubmission::class, 'project_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'project_id');
    }

    public function funding(): HasMany
    {
        return $this->hasMany(FundingRecord::class, 'project_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'project_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'project_id');
    }

    public function dataRoomDocuments(): HasMany
    {
        return $this->hasMany(DataRoomDocument::class, 'project_id');
    }
}
