<?php

namespace App\Models;

use App\Enums\ProgramStatus;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Program extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'name',
        'slug',
        'image',
        'description',
        'objectives',
        'target',
        'capacity',
        'start_date',
        'end_date',
        'registration_start',
        'registration_end',
        'level_id',
        'eligibility',
        'status',
        'is_active',
        'admission_threshold',
        'waitlist_threshold',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $program): void {
            if (blank($program->slug)) {
                $baseSlug = Str::slug($program->name) ?: 'programme';
                $uniqueSuffix = Str::lower(Str::random(6));
                $program->slug = "{$baseSlug}-{$uniqueSuffix}";
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => ProgramStatus::class,
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_start' => 'date',
            'registration_end' => 'date',
            'capacity' => 'integer',
            'admission_threshold' => 'integer',
            'waitlist_threshold' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function isAcceptingApplications(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->registration_end && $this->registration_end->toDateString() < now()->toDateString()) {
            return false;
        }

        return in_array($this->status, [
            ProgramStatus::OUVERT_CANDIDATURES,
            ProgramStatus::ACTIF,
            ProgramStatus::LANCE,
        ], true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return Storage::disk('public')->url($this->image);
    }

    public function getAdmissionThreshold(): int
    {
        return $this->admission_threshold ?? $this->sae?->getAdmissionThreshold() ?? 75;
    }

    public function getWaitlistThreshold(): int
    {
        return $this->waitlist_threshold ?? $this->sae?->getWaitlistThreshold() ?? 50;
    }

    public function getPublicDetailUrlAttribute(): string
    {
        return route('public.programme.detail', ['id' => $this->slug ?: $this->id]);
    }

    public function getPublicApplyUrlAttribute(): string
    {
        return route('public.programme.candidater', ['id' => $this->slug ?: $this->id]);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'level_id');
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'program_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProgramPhase::class, 'program_id')->orderBy('position');
    }

    public function evaluationCriteria(): HasMany
    {
        return $this->hasMany(EvaluationCriterion::class, 'program_id')->orderBy('position');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_program')
            ->withPivot(['sae_id', 'role', 'contribution_amount', 'currency'])
            ->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'program_id');
    }

    public function deliverableDefinitions(): HasMany
    {
        return $this->hasMany(DeliverableDefinition::class, 'program_id');
    }
}
