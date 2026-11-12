<?php

namespace App\Services;

use App\Models\McpApiKey;
use App\Models\McpOAuthClient;
use App\Models\McpOAuthCode;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Support\Str;
use InvalidArgumentException;

class McpOAuthService
{
    /**
     * Valide les paramètres initiaux de la requête d'autorisation OAuth.
     *
     * @param  array{response_type?: string, client_id?: string, redirect_uri?: string, scope?: string, state?: string}  $params
     * @return array{client: ?McpOAuthClient, redirect_uri: string, scope: string, state: ?string}
     */
    public function validateAuthorizeParams(array $params): array
    {
        $responseType = $params['response_type'] ?? null;
        if ($responseType !== 'code') {
            throw new InvalidArgumentException("Le paramètre response_type doit valoir 'code'. Reçu : '{$responseType}'.");
        }

        $redirectUri = $params['redirect_uri'] ?? null;
        if (empty($redirectUri) || ! filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Le paramètre redirect_uri est invalide ou absent.');
        }

        $clientId = $params['client_id'] ?? null;
        $client = null;

        if ($clientId) {
            $client = McpOAuthClient::where('client_id', $clientId)->first();

            if (! $client) {
                throw new InvalidArgumentException('Identifiant client OAuth (client_id) inconnu.');
            }

            if (! $client->allowsRedirectUri($redirectUri)) {
                throw new InvalidArgumentException("L'URL de redirection demandée n'est pas autorisée pour ce client OAuth.");
            }
        }

        return [
            'client' => $client,
            'redirect_uri' => $redirectUri,
            'scope' => $params['scope'] ?? 'mcp:sae',
            'state' => $params['state'] ?? null,
        ];
    }

    /**
     * Génère un code d'autorisation unique pour une SAE et un utilisateur validés.
     */
    public function createAuthorizationCode(
        Sae $sae,
        User $user,
        ?McpOAuthClient $client,
        string $redirectUri,
        ?string $scope = 'mcp:sae',
        ?string $codeChallenge = null,
        ?string $codeChallengeMethod = null
    ): string {
        $plainCode = 'ha_auth_'.Str::random(40);
        $codeHash = hash('sha256', $plainCode);

        McpOAuthCode::create([
            'code_hash' => $codeHash,
            'oauth_client_id' => $client?->id,
            'user_id' => $user->id,
            'sae_id' => $sae->id,
            'redirect_uri' => $redirectUri,
            'scope' => $scope ?? 'mcp:sae',
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => $codeChallengeMethod,
            'expires_at' => now()->addMinutes(10),
        ]);

        return $plainCode;
    }

    /**
     * Échange un code d'autorisation contre un token d'accès Bearer durable.
     *
     * @return array{access_token: string, token_type: string, expires_in: int, scope: string, sae: array{id: int, name: string, slug: string}}
     */
    public function exchangeCodeForAccessToken(
        string $code,
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $redirectUri = null,
        ?string $codeVerifier = null
    ): array {
        $codeHash = hash('sha256', trim($code));

        /** @var McpOAuthCode|null $authCode */
        $authCode = McpOAuthCode::with(['client', 'user', 'sae'])
            ->where('code_hash', $codeHash)
            ->first();

        if (! $authCode) {
            throw new InvalidArgumentException("Code d'autorisation invalide ou déjà consommé.");
        }

        // Le code d'autorisation est à usage unique : on le supprime immédiatement
        $authCode->delete();

        if ($authCode->isExpired()) {
            throw new InvalidArgumentException("Ce code d'autorisation a expiré.");
        }

        if ($redirectUri && $authCode->redirect_uri !== $redirectUri) {
            throw new InvalidArgumentException("L'URL de redirection ne correspond pas à celle demandée lors de l'autorisation.");
        }

        if (! $authCode->verifyCodeVerifier($codeVerifier)) {
            throw new InvalidArgumentException('Échec de vérification du challenge PKCE (code_verifier).');
        }

        if ($authCode->client) {
            if ($clientId && $authCode->client->client_id !== $clientId) {
                throw new InvalidArgumentException('Identifiant client OAuth non concordant.');
            }

            $requiresSecret = $authCode->client->is_confidential && empty($authCode->code_challenge);
            if (($requiresSecret || ! empty($clientSecret)) && ! $authCode->client->matchesSecret($clientSecret)) {
                throw new InvalidArgumentException('Secret client OAuth invalide.');
            }
        }

        $clientLabel = $authCode->client?->name ?? 'Connecteur IA (Claude / ChatGPT)';
        $tokenResult = McpApiKey::createToken(
            sae: $authCode->sae,
            user: $authCode->user,
            name: "Connecteur IA — {$clientLabel}",
            expiresAt: now()->addYear()
        );

        return [
            'access_token' => $tokenResult['plainTextToken'],
            'token_type' => 'Bearer',
            'expires_in' => 31536000,
            'scope' => $authCode->scope,
            'sae' => [
                'id' => $authCode->sae->id,
                'name' => $authCode->sae->name,
                'slug' => $authCode->sae->slug,
            ],
        ];
    }
}
