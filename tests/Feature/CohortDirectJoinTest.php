<?php

use App\Enums\CohortStatus;
use App\Enums\ProgramStatus;
use App\Livewire\Public\ProgramApplicationWizard;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->sae = Sae::first() ?? Sae::create([
        'name' => 'SAE Teranga Africa',
        'slug' => 'teranga-africa',
    ]);

    $this->program = Program::create([
        'sae_id' => $this->sae->id,
        'name' => 'Programme AgTech Panafricain',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 25,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);

    $this->cohort = Cohort::create([
        'sae_id' => $this->sae->id,
        'program_id' => $this->program->id,
        'name' => 'Promotion AgTech Printemps 2026',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 20,
        'start_date' => now()->addMonth(),
        'end_date' => now()->addMonths(6),
    ]);
});

test('un visiteur non connecte peut acceder a la passerelle de candidature d une cohorte', function () {
    $response = $this->get(route('public.cohort.join', $this->cohort->id));

    $response->assertStatus(200);
    $response->assertSee($this->cohort->name);
    $response->assertSee($this->program->name);
    $response->assertSee("J'ai déjà un compte", false);
    $response->assertSee('Créer mon compte candidat');
});

test('un utilisateur deja connecte est redirige directement vers le formulaire de candidature de la cohorte', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('public.cohort.join', $this->cohort->id));

    $response->assertRedirect(route('public.programme.candidater', [
        'id' => $this->program->id,
        'cohort' => $this->cohort->id,
    ]));
});

test('un utilisateur existant peut se connecter via la passerelle de cohorte et etre redirige vers la candidature', function () {
    $user = User::factory()->create([
        'email' => 'entrepreneur.existant@example.com',
        'password' => Hash::make('Secret12345!'),
    ]);

    $response = $this->post(route('public.cohort.login', $this->cohort->id), [
        'email' => 'entrepreneur.existant@example.com',
        'password' => 'Secret12345!',
    ]);

    $response->assertRedirect(route('public.programme.candidater', [
        'id' => $this->program->id,
        'cohort' => $this->cohort->id,
    ]));

    expect(Auth::check())->toBeTrue();
    expect(Auth::id())->toBe($user->id);
});

test('un nouveau candidat peut creer un compte depuis la passerelle de cohorte et etre redirige vers la candidature', function () {
    $response = $this->post(route('public.cohort.register', $this->cohort->id), [
        'first_name' => 'Fatou',
        'last_name' => 'Ndiaye',
        'email' => 'fatou.ndiaye@startup.sn',
        'phone' => '+221 77 123 45 67',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect(route('public.programme.candidater', [
        'id' => $this->program->id,
        'cohort' => $this->cohort->id,
    ]));

    expect(Auth::check())->toBeTrue();

    $user = User::where('email', 'fatou.ndiaye@startup.sn')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Fatou Ndiaye');

    $entrepreneur = Entrepreneur::where('user_id', $user->id)->first();
    expect($entrepreneur)->not->toBeNull();
    expect($entrepreneur->sae_id)->toBe($this->sae->id);
    expect($entrepreneur->phone)->toBe('+221 77 123 45 67');

    expect($user->saes()->where('saes.id', $this->sae->id)->exists())->toBeTrue();
});

test('le wizard de candidature prend en compte la cohorte ciblee lors de la soumission', function () {
    $user = User::factory()->create([
        'name' => 'Moussa Diop',
        'email' => 'moussa.diop@test.com',
    ]);

    $entrepreneur = Entrepreneur::create([
        'user_id' => $user->id,
        'sae_id' => $this->sae->id,
        'first_name' => 'Moussa',
        'last_name' => 'Diop',
        'email' => $user->email,
        'phone' => '+221 70 987 65 43',
        'gender' => 'M',
        'city' => 'Dakar',
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($user);

    Livewire::test(ProgramApplicationWizard::class, [
        'program' => $this->program,
        'cohortId' => $this->cohort->id,
    ])
        ->assertSet('selectedCohortId', $this->cohort->id)
        ->set('project_name', 'AgroTech Solar Irrigation')
        ->set('sector', 'Agriculture Connectée')
        ->set('pitch', 'Solution solaire d irrigation intelligente pour les petits producteurs.')
        ->set('team_size', 3)
        ->set('motivation', 'Nous souhaitons bénéficier du mentorat et de l accompagnement technique de cette cohorte.')
        ->call('submitApplication')
        ->assertSet('isSubmitted', true)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('applications', [
        'program_id' => $this->program->id,
        'cohort_id' => $this->cohort->id,
        'entrepreneur_id' => $entrepreneur->id,
    ]);
});

test('la page detail du programme affiche les promotions et le lien pour rejoindre la cohorte ouverte', function () {
    $response = $this->get(route('public.programme.detail', $this->program->id));

    $response->assertStatus(200);
    $response->assertSee('Promotions &amp; Cohortes du Programme', false);
    $response->assertSee($this->cohort->name);
    $response->assertSee('Inscriptions ouvertes');
    $response->assertSee(route('public.cohort.join', $this->cohort->id));
});
