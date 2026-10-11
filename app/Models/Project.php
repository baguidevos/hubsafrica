<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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
        'initial_level_id',
        'current_level_id',
        'current_level_entered_at',
        'exit_level_id',
        'exited_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'sdgs' => 'array',
            'team_size' => 'integer',
            'current_level_entered_at' => 'datetime',
            'exited_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if ($project->current_level_id && ! $project->initial_level_id) {
                $project->initial_level_id = $project->current_level_id;
            }
            if ($project->current_level_id && ! $project->current_level_entered_at) {
                $project->current_level_entered_at = now();
            }
        });

        static::created(function (Project $project) {
            if ($project->current_level_id) {
                ProjectLevelHistory::firstOrCreate(
                    [
                        'sae_id' => $project->sae_id,
                        'project_id' => $project->id,
                        'to_level_id' => $project->current_level_id,
                    ],
                    [
                        'from_level_id' => null,
                        'started_at' => $project->current_level_entered_at ?? now(),
                        'reason' => 'Palier initial d\'entrée dans l\'accompagnement',
                        'validated_by' => auth()->id() ?? $project->sae?->owner_id,
                    ]
                );
            }
        });

        static::updating(function (Project $project) {
            // Détection du changement de palier
            if ($project->isDirty('current_level_id') && $project->current_level_id !== null) {
                $oldLevelId = $project->getOriginal('current_level_id');
                $newLevelId = $project->current_level_id;

                if (! $project->initial_level_id && $oldLevelId) {
                    $project->initial_level_id = $oldLevelId;
                }

                $project->current_level_entered_at = now();

                // Clôturer l'ancien historique ouvert
                ProjectLevelHistory::where('project_id', $project->id)
                    ->whereNull('ended_at')
                    ->update(['ended_at' => now()]);

                // Créer le nouvel historique horodaté
                ProjectLevelHistory::create([
                    'sae_id' => $project->sae_id,
                    'project_id' => $project->id,
                    'from_level_id' => $oldLevelId,
                    'to_level_id' => $newLevelId,
                    'started_at' => now(),
                    'reason' => 'Évolution de palier d\'accompagnement',
                    'validated_by' => auth()->id() ?? $project->sae?->owner_id,
                ]);

                // Journaliser dans la timeline du projet
                $newLevel = Level::find($newLevelId);
                TimelineEvent::create([
                    'sae_id' => $project->sae_id,
                    'project_id' => $project->id,
                    'entrepreneur_id' => $project->entrepreneur_id,
                    'title' => "Progression : Passage au palier « {$newLevel?->name} »",
                    'description' => "Le projet franchit une étape clé et valide son passage au palier {$newLevel?->name}.",
                    'type' => 'MILESTONE',
                    'icon' => 'heroicon-m-chart-bar',
                ]);
            }

            // Détection du passage en statut de sortie (Diplômé, Sorti, Abandonné)
            if ($project->isDirty('status')) {
                $exitStatuses = [ProjectStatus::DIPLOME, ProjectStatus::SORTI, ProjectStatus::ABANDONNE];
                $newStatus = $project->status instanceof ProjectStatus ? $project->status : ProjectStatus::tryFrom((string) $project->status);

                if ($newStatus && in_array($newStatus, $exitStatuses)) {
                    $project->exit_level_id = $project->current_level_id;
                    $project->exited_at = now();

                    ProjectLevelHistory::where('project_id', $project->id)
                        ->whereNull('ended_at')
                        ->update(['ended_at' => now()]);

                    TimelineEvent::create([
                        'sae_id' => $project->sae_id,
                        'project_id' => $project->id,
                        'entrepreneur_id' => $project->entrepreneur_id,
                        'title' => "Fin d'accompagnement : Statut {$newStatus->getLabel()}",
                        'description' => "La startup clôture son parcours au palier {$project->currentLevel?->name} avec le statut {$newStatus->getLabel()}.",
                        'type' => 'MILESTONE',
                        'icon' => $newStatus->getIcon() ?? 'heroicon-m-academic-cap',
                    ]);
                } elseif ($newStatus === ProjectStatus::ACTIF && in_array($project->getOriginal('status'), ['DIPLOME', 'SORTI', 'ABANDONNE'])) {
                    // Réactivation
                    $project->exit_level_id = null;
                    $project->exited_at = null;
                }
            }
        });
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function initialLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'initial_level_id');
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function exitLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'exit_level_id');
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

    public function latestEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class, 'project_id')->latestOfMany();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'project_id');
    }

    public function latestApplication(): HasOne
    {
        return $this->hasOne(Application::class, 'project_id')->latestOfMany();
    }

    public function dataRoomDocuments(): HasMany
    {
        return $this->hasMany(DataRoomDocument::class, 'project_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function indicatorEntries(): HasMany
    {
        return $this->hasMany(IndicatorEntry::class, 'project_id');
    }

    public function coachAssignments(): HasMany
    {
        return $this->assignments();
    }

    public function deliverableSubmissions(): HasMany
    {
        return $this->submissions();
    }

    public function entrepreneurs(): BelongsTo
    {
        return $this->entrepreneur();
    }
}
