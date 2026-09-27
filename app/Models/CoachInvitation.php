<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CoachInvitation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'email',
        'name',
        'token',
        'specialties',
        'max_capacity',
        'is_external',
        'status',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\CoachInvitationStatus::class,
            'specialties' => 'array',
            'is_external' => 'boolean',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CoachInvitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(48);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;
        return $this->expires_at->isPast() || $statusVal === 'EXPIRED';
    }

    public function isPending(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;
        return $statusVal === 'PENDING' && ! $this->isExpired();
    }
}
