<?php

use App\Enums\FormType;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
use App\Models\Form;
use App\Models\Level;
use App\Models\Sae;
use App\Models\User;
use App\Services\SaeOnboardingService;
use Livewire\Livewire;

test('la page publique rejoindre contient le lien vers le formulaire d inscription SAE', function () {
    $response = $this->get(route('public.rejoindre'));

    $response->assertSuccessful();
    $response->assertSee('/sae/register');
});

test('la page d inscription SAE est accessible pour un visiteur non authentifie', function () {
    $response = $this->get('/sae/register');

    $response->assertSuccessful();
    $response->assertSee('Créer un compte Responsable SAE');
});

test('l enregistrement d une SAE cree automatiquement son formulaire de candidature et ses niveaux', function () {
    $user = User::factory()->create([
        'email' => 'nouveau.responsable@test-sae.com',
    ]);

    $this->actingAs($user);

    Livewire::test(RegisterSae::class)
        ->fillForm([
            'name' => 'Incubateur Sahel Innovation',
            'slug' => 'sahel-innovation',
            'email' => 'contact@sahelinnovation.org',
            'website' => 'https://sahelinnovation.org',
            'phone' => '+221 77 123 45 67',
            'address' => 'Dakar, Sénégal',
            'description' => 'Structure dédiée aux innovations technologiques et agro-écologiques.',
            'vision' => '<p>Bâtir l\'écosystème de référence sahélien.</p>',
            'mission' => '<p>Accompagner 100 startups vers l\'autonomie.</p>',
            'objectives' => '<p>Créer 500 emplois verts.</p>',
            'services' => '<p>Incubation, accélération et mentorat.</p>',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $sae = Sae::where('slug', 'sahel-innovation')->first();
    expect($sae)->not->toBeNull();
    expect($sae->owner_id)->toBe($user->id);
    expect($sae->vision)->toContain('écosystème de référence');
    expect($sae->mission)->toContain('100 startups');
    expect($sae->objectives)->toContain('500 emplois');

    // Vérification de la création automatique du formulaire d'inscription / candidature pour la SAE
    $form = Form::withoutGlobalScopes()
        ->where('sae_id', $sae->id)
        ->where('type', FormType::CANDIDATURE)
        ->first();

    expect($form)->not->toBeNull()
        ->and($form->name)->toContain('Dossier de Candidature & Inscription')
        ->and($form->is_template)->toBeTrue()
        ->and($form->is_standard)->toBeFalse()
        ->and(count($form->fields))->toBeGreaterThan(0);

    // Vérification des niveaux de maturité par défaut
    $levels = Level::where('sae_id', $sae->id)->orderBy('order')->get();
    expect($levels->count())->toBe(3)
        ->and($levels[0]->name)->toContain('Idéation')
        ->and($levels[1]->name)->toContain('Incubation')
        ->and($levels[2]->name)->toContain('Accélération');
});

test('le service SaeOnboardingService initialise correctement les formulaires et niveaux sans doublon', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Hub Dakar Tech',
        'slug' => 'hub-dakar-tech',
        'owner_id' => $user->id,
    ]);

    $service = app(SaeOnboardingService::class);
    $form1 = $service->initializeSaeDefaults($sae, $user);

    expect($form1)->not->toBeNull()
        ->and($form1->sae_id)->toBe($sae->id);

    // Appel idempotent : ne doit pas créer de doublon de formulaire ni de niveaux
    $form2 = $service->initializeSaeDefaults($sae, $user);
    expect($form2->id)->toBe($form1->id);

    $formCount = Form::withoutGlobalScopes()
        ->where('sae_id', $sae->id)
        ->where('type', FormType::CANDIDATURE)
        ->count();

    expect($formCount)->toBe(1);
    expect($sae->levels()->count())->toBe(3);
});
