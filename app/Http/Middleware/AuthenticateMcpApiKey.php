<?php

namespace App\Http\Middleware;

use App\Models\McpApiKey;
use App\Models\Sae;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMcpApiKey
{
    /**
     * Valide la clé API MCP transmise par l'agent IA (Claude, ChatGPT, etc.)
     * et initialise le contexte d'isolation de la structure (SAE).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Empêcher la fuite de détails techniques (stack traces, noms de modèles) vers les clients IA
        config(['app.debug' => false]);

        $token = $request->bearerToken()
            ?: $request->header('X-API-KEY')
            ?: $request->query('api_key')
            ?: $request->query('token');

        if (! $token || ! is_string($token)) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32000,
                    'message' => 'Authentification requise. Veuillez transmettre un jeton valide via Authorization: Bearer <ha_mcp_live_...>',
                ],
            ], 401, ['WWW-Authenticate' => 'Bearer realm="Hubs Africa MCP", resource_metadata="'.url('/.well-known/oauth-protected-resource').'"']);
        }

        $hash = hash('sha256', trim($token));

        /** @var McpApiKey|null $apiKey */
        $apiKey = McpApiKey::with(['sae', 'user'])
            ->where('key_hash', $hash)
            ->first();

        if (! $apiKey || $apiKey->isExpired()) {
            return response()->json([
                'jsonrpc' => '2.0',
                'error' => [
                    'code' => -32001,
                    'message' => 'Clé API MCP invalide ou expirée.',
                ],
            ], 401, ['WWW-Authenticate' => 'Bearer error="invalid_token"']);
        }

        $apiKey->updateQuietly(['last_used_at' => now()]);

        // Définition de l'utilisateur authentifié et liaison de la SAE dans le conteneur
        if ($apiKey->user) {
            auth()->setUser($apiKey->user);
        }

        app()->instance('current_sae', $apiKey->sae);
        app()->instance(Sae::class, $apiKey->sae);
        app()->instance(McpApiKey::class, $apiKey);

        return $next($request);
    }
}
