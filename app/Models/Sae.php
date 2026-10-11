<?php

namespace App\Models;

use App\Enums\SaeOriginType;
use Filament\Models\Contracts\HasAvatar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Sae extends Model implements HasAvatar
{
    use HasFactory;

    protected $attributes = [
        'origin_type' => 'INDEPENDANTE',
    ];

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'email',
        'website',
        'phone',
        'address',
        'country',
        'city',
        'origin_type',
        'university_name',
        'logo',
        'status',
        'default_admission_threshold',
        'default_waitlist_threshold',
        'vision',
        'mission',
        'objectives',
        'services',
    ];

    protected function casts(): array
    {
        return [
            'origin_type' => SaeOriginType::class,
            'default_admission_threshold' => 'integer',
            'default_waitlist_threshold' => 'integer',
        ];
    }

    public function getAdmissionThreshold(): int
    {
        return $this->default_admission_threshold ?? 75;
    }

    public function getWaitlistThreshold(): int
    {
        return $this->default_waitlist_threshold ?? 50;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return Storage::disk('public')->url($this->logo);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->getFilamentAvatarUrl();
    }

    public function isUniversity(): bool
    {
        return $this->origin_type === SaeOriginType::UNIVERSITAIRE;
    }

    public function isIndependent(): bool
    {
        return $this->origin_type === SaeOriginType::INDEPENDANTE || is_null($this->origin_type);
    }

    public function getOriginLabelAttribute(): string
    {
        if ($this->origin_type instanceof SaeOriginType) {
            return $this->origin_type->getLabel();
        }

        return 'Structure indépendante';
    }

    public function getProvenanceLabelAttribute(): string
    {
        $parts = array_filter([$this->city, $this->country]);

        return ! empty($parts) ? implode(', ', $parts) : ($this->address ?: 'Non renseigné');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Sae $sae) {
            if (empty($sae->slug)) {
                $sae->slug = Str::slug($sae->name);
            }
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sae_users')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function types(): BelongsToMany
    {
        return $this->belongsToMany(SaeType::class, 'sae_sae_type')
            ->withTimestamps();
    }

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class, 'sae_id');
    }

    public function coachInvitations(): HasMany
    {
        return $this->hasMany(CoachInvitation::class, 'sae_id');
    }

    public function entrepreneurs(): HasMany
    {
        return $this->hasMany(Entrepreneur::class, 'sae_id');
    }

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class, 'sae_id');
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'sae_id');
    }

    public function cohorts(): HasMany
    {
        return $this->hasMany(Cohort::class, 'sae_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'sae_id');
    }

    public function deliverableDefinitions(): HasMany
    {
        return $this->hasMany(DeliverableDefinition::class, 'sae_id');
    }

    public function coachingSessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'sae_id');
    }

    public function fundingRecords(): HasMany
    {
        return $this->hasMany(FundingRecord::class, 'sae_id');
    }

    public function indicators(): HasMany
    {
        return $this->hasMany(Indicator::class, 'sae_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'sae_id');
    }

    public function dataRoomDocuments(): HasMany
    {
        return $this->hasMany(DataRoomDocument::class, 'sae_id');
    }

    public function formUnlocks(): HasMany
    {
        return $this->hasMany(FormUnlock::class, 'sae_id');
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class, 'sae_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'sae_id');
    }
}
