<?php

namespace App\Models;

use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Models\Concerns\BelongsToTenant;
use App\Support\EntrepreneurCompletionChecker;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Entrepreneur extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'country',
        'territory',
        'city',
        'education_level',
        'profession',
        'professional_status',
        'legal_status',
        'notes',
        'photo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => EntrepreneurStatus::class,
            'gender' => Gender::class,
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $entrepreneur): void {
            EntrepreneurCompletionChecker::forgetForEntrepreneur($entrepreneur);
        });

        static::deleting(function (self $entrepreneur): void {
            Project::withoutGlobalScopes()
                ->where('entrepreneur_id', $entrepreneur->id)
                ->get()
                ->each
                ->delete();
        });

        static::deleted(function (self $entrepreneur): void {
            EntrepreneurCompletionChecker::forgetForEntrepreneur($entrepreneur);

            if (! $entrepreneur->user_id) {
                return;
            }

            $user = User::find($entrepreneur->user_id);

            if (! $user) {
                return;
            }

            DB::table('sae_users')
                ->where('user_id', $user->id)
                ->where('sae_id', $entrepreneur->sae_id)
                ->where('role', 'ENTREPRENEUR')
                ->delete();

            if (self::canDeleteAssociatedUser($user, (int) $entrepreneur->id)) {
                $user->delete();
            }
        });
    }

    private static function canDeleteAssociatedUser(User $user, int $deletedEntrepreneurId): bool
    {
        if ($user->isSuperAdmin() || $user->isSaeManager() || $user->isCoach()) {
            return false;
        }

        $hasOtherEntrepreneurProfile = self::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('id', '!=', $deletedEntrepreneurId)
            ->exists();

        if ($hasOtherEntrepreneurProfile) {
            return false;
        }

        return ! DB::table('sae_users')
            ->where('user_id', $user->id)
            ->where('role', '!=', 'ENTREPRENEUR')
            ->exists();
    }

    public function getCityAttribute(): ?string
    {
        return $this->attributes['territory'] ?? null;
    }

    public function setCityAttribute(?string $value): void
    {
        $this->attributes['territory'] = $value;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'entrepreneur_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'entrepreneur_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(DeliverableSubmission::class, 'entrepreneur_id');
    }

    public function dataRoomDocuments(): HasMany
    {
        return $this->hasMany(DataRoomDocument::class, 'entrepreneur_id');
    }

    public function formUnlocks(): HasMany
    {
        return $this->hasMany(FormUnlock::class, 'entrepreneur_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class, 'entrepreneur_id');
    }

    public function cohorts(): BelongsToMany
    {
        return $this->belongsToMany(Cohort::class, 'enrollments', 'entrepreneur_id', 'cohort_id')
            ->withPivot(['status', 'enrolled_at', 'project_id'])
            ->withTimestamps();
    }

    public function coachingSessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'entrepreneur_id');
    }
}
