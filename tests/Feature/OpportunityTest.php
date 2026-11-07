<?php

use App\Livewire\Public\OpportunitiesCatalog;
use App\Models\Opportunity;
use App\Models\Sae;
use App\Models\User;
use Livewire\Livewire;

test('opportunity model can be created with valid fields and auto generates slug', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Incubateur Hub Dakar',
        'slug' => 'incubateur-hub-dakar',
    ]);

    $opportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Concours Innovation AgriTech 2026',
        'sectors' => ['Agriculture', 'Tech'],
        'target_zones' => ['Sénégal', 'Mali', 'Côte d\'Ivoire'],
        'details' => '<p>Financement d\'amorçage et mentorat de 6 mois.</p>',
        'learn_more_url' => 'https://example.com/agritech',
        'apply_url' => 'https://example.com/agritech/apply',
        'deadline' => now()->addDays(20),
        'is_active' => true,
    ]);

    expect($opportunity->exists)->toBeTrue()
        ->and($opportunity->slug)->toStartWith('concours-innovation-agritech-2026')
        ->and($opportunity->isOpen())->toBeTrue()
        ->and($opportunity->isClosed())->toBeFalse()
        ->and($opportunity->daysRemaining())->toBeGreaterThanOrEqual(19);
});

test('opportunity status methods accurately reflect closed and open states', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Innov Togo Hub',
        'slug' => 'innov-togo-hub',
    ]);

    // Opportunité active avec deadline future
    $openOpportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Subvention Fintech Ouverte',
        'sectors' => ['Fintech'],
        'target_zones' => ['Togo'],
        'details' => 'Détails subvention',
        'deadline' => now()->addDays(10),
        'is_active' => true,
    ]);

    // Opportunité avec deadline échue
    $expiredOpportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Challenge Énergies Renouvelables Clôturé',
        'sectors' => ['Énergie'],
        'target_zones' => ['Bénin'],
        'details' => 'Détails énergie',
        'deadline' => now()->subDay(),
        'is_active' => true,
    ]);

    // Opportunité désactivée manuellement
    $inactiveOpportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Bourse Entrepreneur Clôturée Manuellement',
        'sectors' => ['Santé'],
        'target_zones' => ['Panafricain'],
        'details' => 'Détails bourse',
        'deadline' => now()->addDays(5),
        'is_active' => false,
    ]);

    expect($openOpportunity->isOpen())->toBeTrue()
        ->and($openOpportunity->isClosed())->toBeFalse()
        ->and($expiredOpportunity->isOpen())->toBeFalse()
        ->and($expiredOpportunity->isClosed())->toBeTrue()
        ->and($inactiveOpportunity->isOpen())->toBeFalse()
        ->and($inactiveOpportunity->isClosed())->toBeTrue();
});

test('opportunity scopes filter published, open and closed items correctly', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Abidjan Business Hub',
        'slug' => 'abidjan-business-hub',
    ]);

    Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Fonds Vert Ouvert',
        'details' => 'Détails du fonds vert',
        'deadline' => now()->addDays(15),
        'is_active' => true,
    ]);

    Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Fonds Tech Échu',
        'details' => 'Détails du fonds tech échu',
        'deadline' => now()->subDays(3),
        'is_active' => true,
    ]);

    Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Bourse Brouillon Non Publiée',
        'details' => 'Détails de la bourse brouillon',
        'deadline' => now()->addDays(30),
        'is_active' => false,
    ]);

    $openCount = Opportunity::withoutGlobalScopes()->open()->count();
    $closedCount = Opportunity::withoutGlobalScopes()->closed()->count();
    $publishedCount = Opportunity::withoutGlobalScopes()->published()->count();

    expect($openCount)->toBe(1)
        ->and($closedCount)->toBe(2)
        ->and($publishedCount)->toBe(2);
});

test('public opportunities catalog page renders and contains active opportunities', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Hub Cameroun Innovation',
        'slug' => 'hub-cameroun-innovation',
    ]);

    Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Appel à Projets Climat Afrique Centrale 2026',
        'sectors' => ['Environnement', 'Climat'],
        'target_zones' => ['Cameroun', 'Gabon'],
        'details' => 'Accompagnement et dotation financière de 50 000 USD.',
        'learn_more_url' => 'https://example.com/climat',
        'apply_url' => 'https://example.com/climat/postuler',
        'deadline' => now()->addDays(25),
        'is_active' => true,
    ]);

    $response = $this->get(route('public.opportunities'));

    $response->assertOk();
    $response->assertSee('Opportunités &amp; Appels à Projets', false);
    $response->assertSee('Appel à Projets Climat Afrique Centrale 2026');
    $response->assertSee('Hub Cameroun Innovation');
});

test('livewire opportunities catalog filters by search, sae and status', function () {
    $userOne = User::factory()->create();
    $saeOne = Sae::create([
        'owner_id' => $userOne->id,
        'name' => 'SAE Dakar Tech',
        'slug' => 'sae-dakar-tech',
    ]);

    $userTwo = User::factory()->create();
    $saeTwo = Sae::create([
        'owner_id' => $userTwo->id,
        'name' => 'SAE Lomé Lab',
        'slug' => 'sae-lome-lab',
    ]);

    Opportunity::create([
        'sae_id' => $saeOne->id,
        'title' => 'Programme Accélération IA Dakar',
        'sectors' => ['Intelligence Artificielle'],
        'target_zones' => ['Sénégal'],
        'details' => 'Mentorat d\'experts IA',
        'deadline' => now()->addDays(12),
        'is_active' => true,
    ]);

    Opportunity::create([
        'sae_id' => $saeTwo->id,
        'title' => 'Concours E-santé Lomé Clôturé',
        'sectors' => ['Santé'],
        'target_zones' => ['Togo'],
        'details' => 'Challenge médical',
        'deadline' => now()->subDay(),
        'is_active' => true,
    ]);

    Livewire::test(OpportunitiesCatalog::class)
        ->assertSee('Programme Accélération IA Dakar')
        ->assertSee('Concours E-santé Lomé Clôturé')
        ->set('search', 'Dakar')
        ->assertSee('Programme Accélération IA Dakar')
        ->assertDontSee('Concours E-santé Lomé Clôturé')
        ->set('search', '')
        ->set('selectedStatus', 'closed')
        ->assertSee('Concours E-santé Lomé Clôturé')
        ->assertDontSee('Programme Accélération IA Dakar');
});

test('closed opportunity displays in red with appropriate closed badges and notices', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Cotonou Startup Hub',
        'slug' => 'cotonou-startup-hub',
    ]);

    $closedOpp = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Concours FinTech Bénin Clôturé',
        'sectors' => ['FinTech'],
        'target_zones' => ['Bénin'],
        'details' => 'Ce concours est désormais clos.',
        'deadline' => now()->subDays(5),
        'is_active' => true,
    ]);

    // Sur le catalogue Livewire
    Livewire::test(OpportunitiesCatalog::class)
        ->assertSee('Concours FinTech Bénin Clôturé')
        ->assertSee('Clôturé')
        ->assertSee('border-red-300');

    // Sur la page de détail
    $response = $this->get(route('public.opportunities.detail', $closedOpp->slug));
    $response->assertOk();
    $response->assertSee('Clôturé');
    $response->assertSee('Candidatures closes');
    $response->assertSee('bg-red-600');
    $response->assertSee('Cette opportunité a atteint sa date limite de dépôt');
});

test('opportunity can have cover image and renders correctly on catalog and detail pages', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Kigali Innovation City Hub',
        'slug' => 'kigali-innovation-city-hub',
    ]);

    $opportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Challenge AgriTech Rwanda 2026',
        'image' => 'opportunities/covers/agritech-rwanda.jpg',
        'sectors' => ['AgriTech'],
        'target_zones' => ['Rwanda'],
        'details' => 'Dotation de 20 000 USD pour les lauréats.',
        'deadline' => now()->addDays(15),
        'is_active' => true,
    ]);

    expect($opportunity->image)->toBe('opportunities/covers/agritech-rwanda.jpg')
        ->and($opportunity->image_url)->toContain('opportunities/covers/agritech-rwanda.jpg');

    // Catalogue Livewire
    Livewire::test(OpportunitiesCatalog::class)
        ->assertSee('Challenge AgriTech Rwanda 2026')
        ->assertSee('opportunities/covers/agritech-rwanda.jpg');

    // Page de détail
    $response = $this->get(route('public.opportunities.detail', $opportunity->slug));
    $response->assertOk();
    $response->assertSee('Challenge AgriTech Rwanda 2026');
    $response->assertSee('opportunities/covers/agritech-rwanda.jpg');
});

test('opportunity provides secure opaque share urls and multi-channel share links', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Ouaga Innovation Hub',
        'slug' => 'ouaga-innovation-hub',
    ]);

    $opportunity = Opportunity::create([
        'sae_id' => $sae->id,
        'title' => 'Bourse Climat Sahel 2026',
        'sectors' => ['Climat', 'Environnement'],
        'target_zones' => ['Burkina Faso', 'Niger'],
        'details' => 'Accompagnement et dotation financière.',
        'deadline' => now()->addDays(30),
        'is_active' => true,
    ]);

    // L'URL de partage ne doit pas révéler l'ID interne de la base de données
    expect($opportunity->share_url)
        ->toContain('/opportunites/'.$opportunity->slug)
        ->not->toContain('id='.$opportunity->id)
        ->not->toContain('sae_id=');

    // Les liens de réseaux sociaux doivent être pré-formatés et encodés
    $shareLinks = $opportunity->share_links;
    expect($shareLinks)->toHaveKeys(['copy', 'whatsapp', 'linkedin', 'twitter', 'facebook', 'email'])
        ->and($shareLinks['whatsapp'])->toContain('https://api.whatsapp.com/send?text=')
        ->and($shareLinks['linkedin'])->toContain('https://www.linkedin.com/sharing/share-offsite/?url=')
        ->and($shareLinks['twitter'])->toContain('https://twitter.com/intent/tweet?url=')
        ->and($shareLinks['email'])->toContain('mailto:?subject=');

    // Le catalogue doit contenir l'action de partage
    Livewire::test(OpportunitiesCatalog::class)
        ->assertSee('Bourse Climat Sahel 2026')
        ->assertSee('Partager');

    // La page de détail doit afficher le widget social interactif
    $response = $this->get(route('public.opportunities.detail', $opportunity->slug));
    $response->assertOk()
        ->assertSee("Partager l'opportunité", false)
        ->assertSee('WhatsApp')
        ->assertSee('LinkedIn')
        ->assertSee('Copier le lien public sécurisé');
});
