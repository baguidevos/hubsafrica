<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormDataRoomShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_sae_id',
        'to_sae_id',
        'entrepreneur_id',
        'shared_by_id',
        'message',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'revoked_at' => 'datetime',
        ];
    }

    public function fromSae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'from_sae_id');
    }

    public function toSae(): BelongsTo
    {
        return $this->belongsTo(Sae::class, 'to_sae_id');
    }

    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(Entrepreneur::class, 'entrepreneur_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_id');
    }

    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }
}
