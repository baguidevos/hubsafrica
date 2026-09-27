<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sae extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'description',
        'email',
        'phone',
        'address',
        'logo',
        'status',
    ];

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
