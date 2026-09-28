<?php

use App\Models\User;

test('comptes test overview page renders successfully', function () {
    $response = $this->get('/comptes-test');

    $response->assertOk();
    $response->assertSee('Comptes de Test');
    $response->assertSee('admin@hubafrica.org');
    $response->assertSee('fatou.diop@teranga.sn');
    $response->assertSee('marie.sarr@coach.sn');
    $response->assertSee('ousmane.fall@ecobat.sn');
});

test('auto login connects super admin and redirects to admin panel', function () {
    $admin = User::firstOrCreate(
        ['email' => 'admin@hubafrica.org'],
        ['name' => 'Directeur Hub Africa', 'password' => bcrypt('password')]
    );

    $response = $this->get('/demo/login?as=admin');

    $response->assertRedirect('/admin');
    $this->assertAuthenticatedAs($admin);
});

test('auto login connects sae manager and redirects to sae dashboard', function () {
    $user = User::firstOrCreate(
        ['email' => 'fatou.diop@teranga.sn'],
        ['name' => 'Fatou Diop', 'password' => bcrypt('password')]
    );

    $response = $this->get('/demo/login?as=sae');

    $response->assertRedirect('/sae/teranga-africa');
    $this->assertAuthenticatedAs($user);
});

test('auto login connects coach and redirects to coach dashboard', function () {
    $user = User::firstOrCreate(
        ['email' => 'marie.sarr@coach.sn'],
        ['name' => 'Marie Sarr', 'password' => bcrypt('password')]
    );

    $response = $this->get('/demo/login?as=coach');

    $response->assertRedirect('/coach/teranga-africa');
    $this->assertAuthenticatedAs($user);
});

test('auto login connects entrepreneur and redirects to portal dashboard', function () {
    $user = User::firstOrCreate(
        ['email' => 'ousmane.fall@ecobat.sn'],
        ['name' => 'Ousmane Fall', 'password' => bcrypt('password')]
    );

    $response = $this->get('/demo/login?as=entrepreneur');

    $response->assertRedirect('/portal/teranga-africa');
    $this->assertAuthenticatedAs($user);
});

test('logout route disconnects authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/demo/logout');

    $this->assertGuest();
    $response->assertRedirect(route('dev.comptes'));
});

test('mentor url redirects to coach panel path', function () {
    $response = $this->get('/mentor');

    $response->assertRedirect('/coach');
});
