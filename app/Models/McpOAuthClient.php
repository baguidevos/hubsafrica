<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class McpOAuthClient extends Model
{
    use HasFactory;

    protected $table = 'mcp_oauth_clients';

    protected $fillable = [
        'sae_id',
        'name',
        'client_id',
        'client_secret',
        'redirect_uris',
        'is_confidential',
    ];

    protected $casts = [
        'redirect_uris' => 'array',
        'is_confidential' => 'boolean',
    ];

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(McpOAuthCode::class, 'oauth_client_id');
    }

    public function matchesSecret(?string $secret): bool
    {
        if (! $this->is_confidential) {
            return true;
        }

        if (empty($this->client_secret)) {
            return true;
        }

        return hash_equals($this->client_secret, (string) $secret);
    }

    public function allowsRedirectUri(string $redirectUri): bool
    {
        if (empty($this->redirect_uris)) {
            return true;
        }

        return in_array($redirectUri, $this->redirect_uris, true);
    }

    /**
     * Crée ou retrouve le client OAuth dédié pour une SAE.
     */
    public static function findOrCreateForSae(Sae $sae): self
    {
        /** @var self|null $client */
        $client = self::where('sae_id', $sae->id)->first();

        if ($client) {
            return $client;
        }

        return self::create([
            'sae_id' => $sae->id,
            'name' => "Connecteur IA — {$sae->name}",
            'client_id' => 'ha_client_'.Str::lower(Str::random(24)),
            'client_secret' => 'ha_sec_'.Str::random(40),
            'redirect_uris' => null,
            'is_confidential' => true,
        ]);
    }
}
