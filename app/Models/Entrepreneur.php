<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
            'status' => \App\Enums\EntrepreneurStatus::class,
            'gender' => \App\Enums\Gender::class,
            'birth_date' => 'date',
        ];
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

    public function coachingSessions(): HasMany
    {
        return $this->hasMany(CoachingSession::class, 'entrepreneur_id');
    }
}
