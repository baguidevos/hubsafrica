<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class McpApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'sae_id',
        'user_id',
        'name',
        'key_hash',
        'key_prefix',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Crée une nouvelle clé API et retourne l'instance enregistrée ainsi que le jeton en clair.
     *
     * @return array{key: self, plainTextToken: string}
     */
    public static function createToken(Sae $sae, User $user, string $name, ?DateTimeInterface $expiresAt = null): array
    {
        $plainTextToken = 'ha_mcp_live_'.Str::random(40);
        $keyHash = hash('sha256', $plainTextToken);
        $keyPrefix = substr($plainTextToken, 0, 16).'...';

        $record = self::create([
            'sae_id' => $sae->id,
            'user_id' => $user->id,
            'name' => $name,
            'key_hash' => $keyHash,
            'key_prefix' => $keyPrefix,
            'expires_at' => $expiresAt,
        ]);

        return [
            'key' => $record,
            'plainTextToken' => $plainTextToken,
        ];
    }
}
