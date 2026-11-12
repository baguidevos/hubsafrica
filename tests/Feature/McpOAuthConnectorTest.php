<?php

use App\Models\McpOAuthClient;
use App\Models\McpOAuthCode;
use App\Models\Sae;
use App\Models\User;
use App\Services\McpOAuthService;

test('oauth authorize endpoint redirects unauthenticated users to login', function () {
    $response = $this->get('/oauth/authorize?response_type=code&redirect_uri=https://chatgpt.com/callback');

    $response->assertRedirect(route('filament.sae.auth.login'));
});

test('oauth authorize endpoint displays authorization dialog for authenticated user with sae', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Test OAuth',
        'slug' => 'incubateur-test-oauth-'.uniqid(),
    ]);

    $client = McpOAuthClient::findOrCreateForSae($sae);

    $response = $this->actingAs($user)
        ->get("/oauth/authorize?response_type=code&client_id={$client->client_id}&redirect_uri=https://chatgpt.com/callback&state=mystate123");

    $response->assertStatus(200)
        ->assertSee('Connexion de votre Assistant IA')
        ->assertSee('Incubateur Test OAuth')
        ->assertSee('Permissions accordées à l\'IA', false);
});

test('oauth approve generates authorization code and redirects to callback with state', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Test Approve',
        'slug' => 'incubateur-test-approve-'.uniqid(),
    ]);

    $client = McpOAuthClient::findOrCreateForSae($sae);

    $response = $this->actingAs($user)
        ->post('/oauth/authorize/approve', [
            'sae_id' => $sae->id,
            'client_id' => $client->client_id,
            'redirect_uri' => 'https://chatgpt.com/callback',
            'state' => 'random_state_xyz',
            'scope' => 'mcp:sae',
        ]);

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');

    expect($targetUrl)->toContain('https://chatgpt.com/callback')
        ->toContain('code=ha_auth_')
        ->toContain('state=random_state_xyz');

    $this->assertDatabaseHas('mcp_oauth_codes', [
        'sae_id' => $sae->id,
        'user_id' => $user->id,
        'redirect_uri' => 'https://chatgpt.com/callback',
    ]);
});

test('oauth token endpoint successfully exchanges authorization code for bearer access token', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Token Exchange',
        'slug' => 'incubateur-token-exchange-'.uniqid(),
    ]);

    $client = McpOAuthClient::findOrCreateForSae($sae);

    // 1. Simuler l'approbation pour obtenir le code en clair
    $plainCode = app(McpOAuthService::class)->createAuthorizationCode(
        sae: $sae,
        user: $user,
        client: $client,
        redirectUri: 'https://chatgpt.com/callback',
        scope: 'mcp:sae'
    );

    // 2. Échange contre token
    $response = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $plainCode,
        'client_id' => $client->client_id,
        'client_secret' => $client->client_secret,
        'redirect_uri' => 'https://chatgpt.com/callback',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'scope',
            'sae' => ['id', 'name', 'slug'],
        ]);

    $accessToken = $response->json('access_token');
    expect($accessToken)->toStartWith('ha_mcp_live_');

    // 3. Le code doit être détruit (anti-replay)
    expect(McpOAuthCode::count())->toBe(0);

    // 4. Une seconde tentative avec le même code échoue
    $replayResponse = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $plainCode,
        'client_id' => $client->client_id,
        'client_secret' => $client->client_secret,
    ]);

    $replayResponse->assertStatus(400)
        ->assertJsonPath('error', 'invalid_grant');

    // 5. Le jeton obtenu permet d'interroger les endpoints protégés
    $apiResponse = $this->withHeader('Authorization', 'Bearer '.$accessToken)
        ->getJson('/api/mcp/v1/metrics');

    $apiResponse->assertStatus(200)
        ->assertJsonPath('sae.name', 'Incubateur Token Exchange');
});

test('oauth deny endpoint redirects back to callback with access_denied error', function () {
    $response = $this->post('/oauth/authorize/deny', [
        'redirect_uri' => 'https://chatgpt.com/callback',
        'state' => 'denied_state_456',
    ]);

    $response->assertRedirect();
    $targetUrl = $response->headers->get('Location');

    expect($targetUrl)->toContain('https://chatgpt.com/callback')
        ->toContain('error=access_denied')
        ->toContain('state=denied_state_456');
});

test('oauth discovery endpoints and root authorize token aliases work for claude mcp pkce flow', function () {
    $this->getJson('/.well-known/oauth-authorization-server')
        ->assertStatus(200)
        ->assertJsonStructure(['issuer', 'authorization_endpoint', 'token_endpoint', 'code_challenge_methods_supported']);

    $this->getJson('/.well-known/oauth-protected-resource')
        ->assertStatus(200)
        ->assertJsonStructure(['resource', 'authorization_servers']);

    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Claude PKCE',
        'slug' => 'incubateur-claude-pkce-'.uniqid(),
    ]);

    $client = McpOAuthClient::findOrCreateForSae($sae);
    $verifier = 'dBjftJeZ4CVP-mB92K27uhbUJU1p1r_wW1gFWFOEjXk';
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

    $this->actingAs($user)
        ->get("/authorize?response_type=code&client_id={$client->client_id}&redirect_uri=https%3A%2F%2Fclaude.ai%2Fapi%2Fmcp%2Fauth_callback&code_challenge={$challenge}&code_challenge_method=S256&state=claude_state")
        ->assertStatus(200)
        ->assertSee('Incubateur Claude PKCE');

    $plainCode = app(McpOAuthService::class)->createAuthorizationCode(
        sae: $sae,
        user: $user,
        client: $client,
        redirectUri: 'https://claude.ai/api/mcp/auth_callback',
        scope: 'mcp:sae',
        codeChallenge: $challenge,
        codeChallengeMethod: 'S256'
    );

    $tokenResponse = $this->postJson('/token', [
        'grant_type' => 'authorization_code',
        'code' => $plainCode,
        'client_id' => $client->client_id,
        'redirect_uri' => 'https://claude.ai/api/mcp/auth_callback',
        'code_verifier' => $verifier,
    ]);

    $tokenResponse->assertStatus(200)
        ->assertJsonPath('token_type', 'Bearer');
    expect($tokenResponse->json('access_token'))->toStartWith('ha_mcp_live_');
});

test('oauth authorize returns clean error views instead of type error when params invalid or user has no sae', function () {
    $userWithoutSae = User::factory()->create();

    $this->actingAs($userWithoutSae)
        ->get('/oauth/authorize?response_type=code&redirect_uri=https://chatgpt.com/callback')
        ->assertStatus(403)
        ->assertSee('Impossible d\'établir la connexion', false);

    $this->actingAs($userWithoutSae)
        ->get('/oauth/authorize?response_type=invalid&redirect_uri=https://chatgpt.com/callback')
        ->assertStatus(400)
        ->assertSee('Impossible d\'établir la connexion', false);
});

test('dynamic client registration and cimd client_id work without manual client credentials', function () {
    $this->getJson('/.well-known/oauth-authorization-server')
        ->assertStatus(200)
        ->assertJsonPath('registration_endpoint', url('/oauth/register'))
        ->assertJsonPath('client_id_metadata_document_supported', true);

    // 1. Requête DCR invalide sans redirect_uris
    $this->postJson('/oauth/register', [
        'client_name' => 'Invalid Client',
    ])->assertStatus(400)
        ->assertJsonPath('error', 'invalid_client_metadata');

    // 2. Enregistrement dynamique réussi (Claude / ChatGPT)
    $registerResponse = $this->postJson('/oauth/register', [
        'client_name' => 'Claude Web Connector',
        'redirect_uris' => ['https://claude.ai/api/mcp/auth_callback'],
    ]);

    $registerResponse->assertStatus(201)
        ->assertJsonPath('token_endpoint_auth_method', 'none');

    $dynamicClientId = $registerResponse->json('client_id');
    expect($dynamicClientId)->toStartWith('ha_dcr_');

    // 3. Flux complet OAuth PKCE avec le client dynamique
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur DCR',
        'slug' => 'incubateur-dcr-'.uniqid(),
    ]);

    $verifier = 'dcr-verifier-secret-string-for-pkce-s256-check-12345';
    $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

    $this->actingAs($user)
        ->get("/oauth/authorize?response_type=code&client_id={$dynamicClientId}&redirect_uri=https%3A%2F%2Fclaude.ai%2Fapi%2Fmcp%2Fauth_callback&code_challenge={$challenge}&code_challenge_method=S256")
        ->assertStatus(200)
        ->assertSee('Incubateur DCR');

    $approveResponse = $this->actingAs($user)->post('/oauth/authorize/approve', [
        'sae_id' => $sae->id,
        'client_id' => $dynamicClientId,
        'redirect_uri' => 'https://claude.ai/api/mcp/auth_callback',
        'code_challenge' => $challenge,
        'code_challenge_method' => 'S256',
        'state' => 'dcr_state_1',
    ]);

    $approveResponse->assertRedirect();
    parse_str((string) parse_url((string) $approveResponse->headers->get('Location'), PHP_URL_QUERY), $queryParams);
    $authCode = $queryParams['code'] ?? '';

    $tokenResponse = $this->postJson('/oauth/token', [
        'grant_type' => 'authorization_code',
        'code' => $authCode,
        'client_id' => $dynamicClientId,
        'redirect_uri' => 'https://claude.ai/api/mcp/auth_callback',
        'code_verifier' => $verifier,
    ]);

    $tokenResponse->assertStatus(200)
        ->assertJsonPath('sae.name', 'Incubateur DCR');
    expect($tokenResponse->json('access_token'))->toStartWith('ha_mcp_live_');

    // 4. Support CIMD (client_id = URL HTTPS)
    $this->actingAs($user)
        ->get('/oauth/authorize?response_type=code&client_id=https%3A%2F%2Fclaude.ai%2Foauth%2Fclient.json&redirect_uri=https%3A%2F%2Fclaude.ai%2Fapi%2Fmcp%2Fauth_callback')
        ->assertStatus(200)
        ->assertSee('Incubateur DCR');
});
