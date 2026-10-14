<?php

namespace App\Models;

use App\Enums\CoachInvitationStatus;
use App\Enums\CoachJoinRequestStatus;
use App\Mail\CoachJoinRequestAcceptedMail;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Mail;

class CoachJoinRequest extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'user_id',
        'name',
        'email',
        'phone',
        'specialties',
        'bio',
        'linkedin_url',
        'status',
        'rejection_reason',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => CoachJoinRequestStatus::class,
            'specialties' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'sae_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Accepte la demande, génère l'invitation officielle avec token et notifie le coach par email.
     */
    public function accept(User $processor, int $maxCapacity = 5, bool $isExternal = true): CoachInvitation
    {
        $invitation = CoachInvitation::create([
            'sae_id' => $this->sae_id,
            'name' => $this->name,
            'email' => $this->email,
            'specialties' => $this->specialties,
            'max_capacity' => $maxCapacity,
            'is_external' => $isExternal,
            'status' => CoachInvitationStatus::PENDING,
            'created_by' => $processor->id,
        ]);

        $this->update([
            'status' => CoachJoinRequestStatus::ACCEPTED,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);

        Mail::to($this->email)->queue(new CoachJoinRequestAcceptedMail($this, $invitation));

        return $invitation;
    }

    /**
     * Rejette la demande d'adhésion.
     */
    public function reject(User $processor, ?string $reason = null): void
    {
        $this->update([
            'status' => CoachJoinRequestStatus::REJECTED,
            'rejection_reason' => $reason,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ]);
    }
}
