<?php

use App\Models\User;

test('maintenance migrate-force endpoint is protected by key or super admin auth', function () {
    // 1. Without auth or key => 403
    $this->get('/maintenance/migrate-force')->assertForbidden();

    // 2. With wrong key => 403
    $this->get('/maintenance/migrate-force?key=wrong-key')->assertForbidden();

    // 3. With valid secret key => 200
    $response = $this->get('/maintenance/migrate-force?key=hubsafrica-migrate-force-2026');
    $response->assertOk();
    $response->assertSee('=== MIGRATIONS EXÉCUTÉES AVEC SUCCÈS ===', false);

    // 4. Authenticated super admin without query key => 200
    $admin = User::firstOrCreate(
        ['email' => 'admin@hubsafrica.org'],
        ['name' => 'Super Admin', 'password' => 'secret123', 'status' => 'ACTIVE']
    );

    $adminResponse = $this->actingAs($admin)->get('/maintenance/migrate-force');
    $adminResponse->assertOk();
    $adminResponse->assertSee('=== MIGRATIONS EXÉCUTÉES AVEC SUCCÈS ===', false);
});
