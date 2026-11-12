<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\McpOAuthClient;
use App\Models\Sae;
use App\Models\User;
use App\Services\McpOAuthService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class McpOAuthController extends Controller
{
    public function __construct(
        protected McpOAuthService $oauthService
    ) {}

    /**
     * Métadonnées OAuth 2.0 Authorization Server (RFC 8414) pour découverte automatique (Claude, MCP).
     */
    public function authorizationServerMetadata(): JsonResponse
    {
        return response()->json([
            'issuer' => url('/'),
            'authorization_endpoint' => url('/oauth/authorize'),
            'token_endpoint' => url('/oauth/token'),
            'userinfo_endpoint' => url('/api/oauth/userinfo'),
            'response_types_supported' => ['code'],
            'grant_types_supported' => ['authorization_code'],
            'code_challenge_methods_supported' => ['S256', 'plain'],
            'token_endpoint_auth_methods_supported' => ['client_secret_post', 'client_secret_basic', 'none'],
            'scopes_supported' => ['mcp:sae'],
        ]);
    }

    /**
     * Métadonnées OAuth 2.0 Protected Resource (RFC 9728) exigées par la spec MCP.
     */
    public function protectedResourceMetadata(): JsonResponse
    {
        return response()->json([
            'resource' => url('/mcp/sae'),
            'authorization_servers' => [url('/')],
            'scopes_supported' => ['mcp:sae'],
            'bearer_methods_supported' => ['header'],
        ]);
    }

    /**
     * Écran d'autorisation OAuth (affiché dans la popup OpenAI / Claude).
     */
    public function authorize(Request $request): View|RedirectResponse|Response
    {
        if (! auth()->check()) {
            session()->put('url.intended', $request->fullUrl());

            return redirect()->route('filament.sae.auth.login');
        }

        try {
            $validated = $this->oauthService->validateAuthorizeParams($request->all());
        } catch (Exception $e) {
            return response()->view('oauth.error', [
                'errorMessage' => $e->getMessage(),
            ], 400);
        }

        /** @var User $user */
        $user = auth()->user();

        if ($user->hasRole('super_admin')) {
            $userSaes = Sae::orderBy('name')->get();
        } else {
            // Récupérer les SAEs dont l'utilisateur est propriétaire ou membre
            $ownedSaes = Sae::where('owner_id', $user->id)->get();
            $memberSaes = $user->saes()->get();
            $userSaes = $ownedSaes->merge($memberSaes)->unique('id');
        }

        if ($userSaes->isEmpty()) {
            return response()->view('oauth.error', [
                'errorMessage' => "Votre compte ({$user->email}) n'est rattaché à aucune Structure d'Accompagnement (SAE).",
            ], 403);
        }

        // Si le client OAuth est déjà lié à une SAE spécifique, on cible celle-ci
        $client = $validated['client'];
        $selectedSaeId = $client?->sae_id
            ?: (int) $request->query('sae_id')
            ?: $userSaes->first()->id;

        return view('oauth.authorize', [
            'client' => $client,
            'clientName' => $client?->name ?? 'Assistant IA (ChatGPT / Claude)',
            'redirectUri' => $validated['redirect_uri'],
            'scope' => $validated['scope'],
            'state' => $validated['state'],
            'codeChallenge' => $request->query('code_challenge'),
            'codeChallengeMethod' => $request->query('code_challenge_method'),
            'user' => $user,
            'userSaes' => $userSaes,
            'selectedSaeId' => $selectedSaeId,
        ]);
    }

    /**
     * Traitement de l'accord explicite de l'utilisateur.
     */
    public function approve(Request $request): RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('filament.sae.auth.login');
        }

        /** @var User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'sae_id' => 'required|integer|exists:saes,id',
            'redirect_uri' => 'required|url',
            'client_id' => 'nullable|string',
            'scope' => 'nullable|string',
            'state' => 'nullable|string',
            'code_challenge' => 'nullable|string',
            'code_challenge_method' => 'nullable|string',
        ]);

        /** @var Sae|null $sae */
        $sae = Sae::find($validated['sae_id']);

        // Vérification de sécurité : l'utilisateur doit avoir accès à cette SAE
        $hasAccess = $sae && (
            $sae->owner_id === $user->id
            || $user->saes()->where('saes.id', $sae->id)->exists()
            || $user->hasRole('super_admin')
        );

        if (! $hasAccess) {
            abort(403, "Accès non autorisé à cette structure d'accompagnement.");
        }

        $client = ! empty($validated['client_id'])
            ? McpOAuthClient::where('client_id', $validated['client_id'])->first()
            : null;

        $code = $this->oauthService->createAuthorizationCode(
            sae: $sae,
            user: $user,
            client: $client,
            redirectUri: $validated['redirect_uri'],
            scope: $validated['scope'] ?? 'mcp:sae',
            codeChallenge: $validated['code_challenge'] ?? null,
            codeChallengeMethod: $validated['code_challenge_method'] ?? null
        );

        $params = ['code' => $code];
        if (! empty($validated['state'])) {
            $params['state'] = $validated['state'];
        }

        $separator = str_contains($validated['redirect_uri'], '?') ? '&' : '?';

        return redirect()->away($validated['redirect_uri'].$separator.http_build_query($params));
    }

    /**
     * Refus de l'utilisateur.
     */
    public function deny(Request $request): RedirectResponse
    {
        $redirectUri = $request->input('redirect_uri');
        $state = $request->input('state');

        if (! $redirectUri || ! filter_var($redirectUri, FILTER_VALIDATE_URL)) {
            return redirect()->route('home');
        }

        $params = [
            'error' => 'access_denied',
            'error_description' => "L'utilisateur a refusé d'autoriser l'accès à son espace SAE.",
        ];

        if ($state) {
            $params['state'] = $state;
        }

        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return redirect()->away($redirectUri.$separator.http_build_query($params));
    }

    /**
     * Échange du code d'autorisation contre le jeton Bearer (RFC 6749).
     */
    public function token(Request $request): JsonResponse
    {
        $grantType = $request->input('grant_type');

        if ($grantType !== 'authorization_code') {
            return response()->json([
                'error' => 'unsupported_grant_type',
                'error_description' => "Seul le grant_type 'authorization_code' est supporté.",
            ], 400);
        }

        $code = $request->input('code');

        if (empty($code) || ! is_string($code)) {
            return response()->json([
                'error' => 'invalid_request',
                'error_description' => 'Le paramètre code est requis.',
            ], 400);
        }

        // Récupération de client_id et client_secret (soit dans le corps, soit dans le header Basic Auth)
        [$clientId, $clientSecret] = $this->extractClientCredentials($request);

        $redirectUri = $request->input('redirect_uri');
        $codeVerifier = $request->input('code_verifier');

        try {
            $tokenData = $this->oauthService->exchangeCodeForAccessToken(
                code: $code,
                clientId: $clientId,
                clientSecret: $clientSecret,
                redirectUri: $redirectUri,
                codeVerifier: $codeVerifier
            );

            return response()->json($tokenData);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'invalid_grant',
                'error_description' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Profil de l'utilisateur et SAE connectée (RFC OIDC UserInfo).
     */
    public function userInfo(Request $request): JsonResponse
    {
        /** @var Sae|null $sae */
        $sae = app()->bound('current_sae') ? app('current_sae') : null;
        /** @var User|null $user */
        $user = auth()->user();

        return response()->json([
            'sub' => (string) ($user?->id ?? 'sae-user'),
            'name' => $user?->name ?? 'Utilisateur SAE',
            'email' => $user?->email,
            'sae' => $sae ? [
                'id' => $sae->id,
                'name' => $sae->name,
                'slug' => $sae->slug,
            ] : null,
        ]);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    protected function extractClientCredentials(Request $request): array
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        if (! empty($clientId)) {
            return [$clientId, $clientSecret];
        }

        // Support du header Authorization: Basic base64(client_id:client_secret)
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Basic ')) {
            $decoded = base64_decode(substr($authHeader, 6));
            if ($decoded && str_contains($decoded, ':')) {
                [$headerId, $headerSecret] = explode(':', $decoded, 2);

                return [$headerId, $headerSecret];
            }
        }

        return [null, null];
    }
}
