<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpOAuthCode extends Model
{
    use HasFactory;

    protected $table = 'mcp_oauth_codes';

    protected $fillable = [
        'code_hash',
        'oauth_client_id',
        'user_id',
        'sae_id',
        'redirect_uri',
        'scope',
        'code_challenge',
        'code_challenge_method',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(McpOAuthClient::class, 'oauth_client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sae(): BelongsTo
    {
        return $this->belongsTo(Sae::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Valide le code verifier PKCE si un challenge a été fourni lors de l'autorisation.
     */
    public function verifyCodeVerifier(?string $verifier): bool
    {
        if (empty($this->code_challenge)) {
            return true;
        }

        if (empty($verifier)) {
            return false;
        }

        if ($this->code_challenge_method === 'S256') {
            $computedHash = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

            return hash_equals($this->code_challenge, $computedHash);
        }

        return hash_equals($this->code_challenge, $verifier);
    }
}
