<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\ProjectStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Application extends Model
{
    use BelongsToTenant, HasFactory;

    protected static function booted(): void
    {
        static::updated(function (Application $application) {
            if ($application->wasChanged('status') && $application->status === ApplicationStatus::ACCEPTEE) {
                $application->enrollInCohort();
            }
        });
    }

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
     * Enrôle le projet associé dans la cohorte et met à jour son palier/statut d'accompagnement.
     */
    public function enrollInCohort(): ?Enrollment
    {
        if (! $this->cohort_id || ! $this->project_id) {
            return null;
        }

        $enrollment = Enrollment::updateOrCreate(
            [
                'sae_id' => $this->sae_id,
                'cohort_id' => $this->cohort_id,
                'project_id' => $this->project_id,
            ],
            [
                'entrepreneur_id' => $this->entrepreneur_id,
                'status' => 'ACTIF',
                'enrolled_at' => now(),
            ]
        );

        if ($this->project) {
            $targetLevelId = $this->program?->level_id ?? $this->project->current_level_id;
            $this->project->update([
                'status' => ProjectStatus::ACTIF,
                'current_level_id' => $targetLevelId,
                'current_level_entered_at' => $this->project->current_level_entered_at ?? now(),
            ]);

            if ($targetLevelId) {
                ProjectLevelHistory::firstOrCreate(
                    [
                        'sae_id' => $this->sae_id,
                        'project_id' => $this->project_id,
                        'to_level_id' => $targetLevelId,
                    ],
                    [
                        'started_at' => now(),
                        'reason' => "Sélectionné(e) pour le programme {$this->program?->name}",
                        'validated_by' => auth()->id() ?? $this->sae?->owner_id,
                    ]
                );
            }
        }

        return $enrollment;
    }

    public function getAdmissionThreshold(): int
    {
        return $this->program?->getAdmissionThreshold()
            ?? $this->sae?->getAdmissionThreshold()
            ?? 75;
    }

    public function getWaitlistThreshold(): int
    {
        return $this->program?->getWaitlistThreshold()
            ?? $this->sae?->getWaitlistThreshold()
            ?? 50;
    }

    /**
     * Applique la décision d'évaluation, le statut et actualise le projet (enrôlement, timeline, log).
     */
    public function applyEvaluationDecision(int $score, ?ApplicationStatus $status = null, ?string $juryComment = null): void
    {
        $admissionThreshold = $this->getAdmissionThreshold();
        $waitlistThreshold = $this->getWaitlistThreshold();

        $newStatus = $status ?? match (true) {
            $score >= $admissionThreshold => ApplicationStatus::ACCEPTEE,
            $score >= $waitlistThreshold => ApplicationStatus::EN_ATTENTE,
            default => ApplicationStatus::REFUSEE,
        };

        $decisionText = match ($newStatus) {
            ApplicationStatus::ACCEPTEE => "Candidature retenue par le jury avec avis favorable (note : {$score}/100, seuil : {$admissionThreshold}/100)",
            ApplicationStatus::EN_ATTENTE => "Candidature placée sur liste d'attente (note : {$score}/100, seuil : {$waitlistThreshold}/100)",
            ApplicationStatus::REFUSEE => "Dossier non retenu par le comité de sélection (note : {$score}/100)",
            default => "Évalué par le jury (note : {$score}/100)",
        };

        $this->update([
            'score' => $score,
            'status' => $newStatus,
            'reviewed_at' => now(),
            'decision' => $decisionText,
        ]);

        if ($this->project) {
            if ($newStatus === ApplicationStatus::ACCEPTEE) {
                $this->enrollInCohort();

                TimelineEvent::create([
                    'sae_id' => $this->sae_id,
                    'project_id' => $this->project_id,
                    'entrepreneur_id' => $this->entrepreneur_id,
                    'title' => "Candidature Retenue : {$this->program?->name} ({$score}/100)",
                    'description' => "Le jury a validé le dossier avec la note de {$score}/100. Intégration officielle de la promotion {$this->cohort?->name}.".($juryComment ? " Appréciation : « {$juryComment} »" : ''),
                    'type' => 'MILESTONE',
                    'icon' => 'heroicon-m-check-badge',
                ]);
            } elseif ($newStatus === ApplicationStatus::EN_ATTENTE) {
                TimelineEvent::create([
                    'sae_id' => $this->sae_id,
                    'project_id' => $this->project_id,
                    'entrepreneur_id' => $this->entrepreneur_id,
                    'title' => "Candidature en Liste d'Attente ({$score}/100)",
                    'description' => "Dossier examiné par le jury avec une note de {$score}/100 pour le programme {$this->program?->name}. Candidat placé sur liste d'attente.".($juryComment ? " Avis : « {$juryComment} »" : ''),
                    'type' => 'NOTE',
                    'icon' => 'heroicon-m-clock',
                ]);
            } elseif ($newStatus === ApplicationStatus::REFUSEE) {
                if ($this->cohort_id) {
                    Enrollment::where('cohort_id', $this->cohort_id)
                        ->where('project_id', $this->project_id)
                        ->update(['status' => 'ANNULE']);
                }

                TimelineEvent::create([
                    'sae_id' => $this->sae_id,
                    'project_id' => $this->project_id,
                    'entrepreneur_id' => $this->entrepreneur_id,
                    'title' => "Délibération Jury : Non Retenu ({$score}/100)",
                    'description' => "Dossier non retenu pour le programme {$this->program?->name} (note de {$score}/100).".($juryComment ? " Motif : « {$juryComment} »" : ''),
                    'type' => 'NOTE',
                    'icon' => 'heroicon-m-x-circle',
                ]);
            }

            ProjectUpdateLog::create([
                'sae_id' => $this->sae_id,
                'project_id' => $this->project_id,
                'changed_by_role' => auth()->user()?->isCoach() ? 'COACH' : 'SAE',
                'changed_by_name' => auth()->user()?->name ?? 'Comité Jury',
                'changes' => [
                    [
                        'field' => 'statut_candidature',
                        'label' => 'Délibération du Jury',
                        'oldValue' => 'En cours d\'instruction',
                        'newValue' => $newStatus->getLabel(),
                    ],
                    [
                        'field' => 'score_jury',
                        'label' => 'Note consolidée',
                        'oldValue' => 'Non notée',
                        'newValue' => "{$score} / 100",
                    ],
                    [
                        'field' => 'cohorte',
                        'label' => 'Cohorte d\'intégration',
                        'oldValue' => 'Non assignée',
                        'newValue' => $this->cohort?->name ?? 'Cohorte du programme',
                    ],
                ],
            ]);
        }
    }

    /**
     * Recalcule la note globale moyenne de la candidature à partir des évaluations soumises du jury.
     */
    public function recalculateAggregatedScore(?ApplicationStatus $forcedStatus = null): ?int
    {
        $averageScore = $this->evaluations()
            ->whereNotNull('total_score')
            ->avg('total_score');

        if ($averageScore !== null) {
            $rounded = (int) round($averageScore);
            $this->applyEvaluationDecision($rounded, $forcedStatus);

            return $rounded;
        }

        return null;
    }
}
