<?php

use App\Filament\Admin\Resources\Saes\Pages\CreateSae;
use App\Filament\Admin\Resources\Saes\Pages\EditSae;
use App\Filament\Admin\Resources\SaeTypes\Pages\CreateSaeType;
use App\Filament\Admin\Resources\SaeTypes\Pages\ListSaeTypes;
use App\Filament\Coach\Widgets\CoachSaeIdentityWidget;
use App\Filament\Sae\Pages\Tenancy\EditSaeProfile;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
use App\Filament\Sae\Widgets\SaeIdentityWidget;
use App\Models\Coach;
use App\Models\Program;
use App\Models\Role;
use App\Models\Sae;
use App\Models\SaeType;
use App\Models\User;
use Database\Seeders\SaeTypeSeeder;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('le seeder SaeTypeSeeder cree les types fondamentaux de SAE', function () {
    $this->seed(SaeTypeSeeder::class);

    expect(SaeType::where('slug', 'couveuse')->exists())->toBeTrue()
        ->and(SaeType::where('slug', 'espace-coworking')->exists())->toBeTrue()
        ->and(SaeType::where('slug', 'pepiniere')->exists())->toBeTrue()
        ->and(SaeType::where('slug', 'incubateur')->exists())->toBeTrue()
        ->and(SaeType::where('slug', 'accelerateur')->exists())->toBeTrue();
});

test('une SAE peut etre associee a plusieurs types simultanement', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Hub Innov 360',
        'slug' => 'hub-innov-360',
        'owner_id' => $user->id,
    ]);

    $type1 = SaeType::firstOrCreate(['slug' => 'couveuse'], ['name' => 'Couveuse d\'entreprises']);
    $type2 = SaeType::firstOrCreate(['slug' => 'espace-coworking'], ['name' => 'Espace de coworking']);
    $type3 = SaeType::firstOrCreate(['slug' => 'pepiniere'], ['name' => 'Pépinière d\'entreprises']);

    $sae->types()->sync([$type1->id, $type2->id, $type3->id]);

    expect($sae->types()->count())->toBe(3)
        ->and($sae->types->pluck('slug')->toArray())->toContain('couveuse', 'espace-coworking', 'pepiniere');
});

test('le webmaster super admin peut administrer les types de SAE via le panel Admin', function () {
    setPermissionsTeamId(0);
    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::firstOrCreate(
        ['email' => 'admin@hubsafrica.org'],
        ['name' => 'Directeur Hubs Africa', 'password' => bcrypt('password')]
    );
    $admin->assignRole($superAdminRole);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    // Liste des types
    Livewire::test(ListSaeTypes::class)
        ->assertSuccessful();

    // Création d'un nouveau type
    Livewire::test(CreateSaeType::class)
        ->fillForm([
            'name' => 'Living Lab Citoyen',
            'slug' => 'living-lab-citoyen',
            'color' => 'purple',
            'description' => 'Laboratoire d\'expérimentation citoyenne et territoriale.',
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(SaeType::where('slug', 'living-lab-citoyen')->exists())->toBeTrue();

    // Création d'une SAE avec types depuis le formulaire d'administration
    $pepiniere = SaeType::firstOrCreate(['slug' => 'pepiniere'], ['name' => 'Pépinière d\'entreprises']);
    $coworking = SaeType::firstOrCreate(['slug' => 'espace-coworking'], ['name' => 'Espace de coworking']);

    Livewire::test(CreateSae::class)
        ->fillForm([
            'name' => 'Tech Hub Abidjan Admin',
            'slug' => 'tech-hub-abidjan-admin',
            'status' => 'ACTIVE',
            'types' => [$pepiniere->id, $coworking->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $createdSae = Sae::where('slug', 'tech-hub-abidjan-admin')->first();
    expect($createdSae)->not->toBeNull()
        ->and($createdSae->types()->count())->toBe(2)
        ->and($createdSae->types->pluck('slug')->toArray())->toContain('pepiniere', 'espace-coworking');
});

test('l administrateur peut modifier l ensemble des informations d une SAE', function () {
    setPermissionsTeamId(0);
    $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::firstOrCreate(
        ['email' => 'admin@hubsafrica.org'],
        ['name' => 'Directeur Hubs Africa', 'password' => bcrypt('password')]
    );
    $admin->assignRole($superAdminRole);

    $sae = Sae::create([
        'name' => 'Ancien Nom Incubateur',
        'slug' => 'ancien-nom-incubateur',
        'status' => 'INACTIVE',
        'email' => 'old@incubateur.org',
        'phone' => '+221 33 000 00 00',
        'website' => 'https://old.org',
        'address' => 'Ancienne adresse',
        'description' => 'Ancienne description',
        'vision' => '<p>Ancienne vision</p>',
        'mission' => '<p>Ancienne mission</p>',
        'objectives' => '<p>Anciens objectifs</p>',
        'services' => '<p>Anciens services</p>',
        'default_admission_threshold' => 60,
        'default_waitlist_threshold' => 40,
    ]);

    $typeCouveuse = SaeType::firstOrCreate(['slug' => 'couveuse'], ['name' => 'Couveuse d\'entreprises']);
    $typeFabLab = SaeType::firstOrCreate(['slug' => 'fablab-makerspace'], ['name' => 'FabLab / MakerSpace']);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(EditSae::class, ['record' => $sae->id])
        ->fillForm([
            'name' => 'Nouveau Nom Incubateur Innovation',
            'slug' => 'nouveau-nom-incubateur-innovation',
            'status' => 'ACTIVE',
            'email' => 'nouveau@incubateur.org',
            'phone' => '+221 77 999 88 77',
            'website' => 'https://nouveau-incubateur.org',
            'address' => 'Nouvelle adresse Dakar',
            'description' => 'Nouvelle présentation enrichie',
            'vision' => '<p>Nouvelle vision stratégique</p>',
            'mission' => '<p>Nouvelle mission opérationnelle</p>',
            'objectives' => '<p>Nouveaux objectifs</p>',
            'services' => '<p>Nouveaux services</p>',
            'default_admission_threshold' => 80,
            'default_waitlist_threshold' => 55,
            'types' => [$typeCouveuse->id, $typeFabLab->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $sae->refresh();
    expect($sae->name)->toBe('Nouveau Nom Incubateur Innovation')
        ->and($sae->slug)->toBe('nouveau-nom-incubateur-innovation')
        ->and($sae->status)->toBe('ACTIVE')
        ->and($sae->email)->toBe('nouveau@incubateur.org')
        ->and($sae->phone)->toBe('+221 77 999 88 77')
        ->and($sae->website)->toBe('https://nouveau-incubateur.org')
        ->and($sae->address)->toBe('Nouvelle adresse Dakar')
        ->and($sae->default_admission_threshold)->toBe(80)
        ->and($sae->default_waitlist_threshold)->toBe(55)
        ->and($sae->types()->count())->toBe(2)
        ->and($sae->types->pluck('slug')->toArray())->toContain('couveuse', 'fablab-makerspace');
});

test('une SAE peut definir ses types lors de son enregistrement onboarding', function () {
    $user = User::factory()->create([
        'email' => 'directrice@senegal-incub.com',
    ]);

    $this->actingAs($user);

    $couveuse = SaeType::firstOrCreate(['slug' => 'couveuse'], ['name' => 'Couveuse d\'entreprises']);
    $coworking = SaeType::firstOrCreate(['slug' => 'espace-coworking'], ['name' => 'Espace de coworking']);

    Livewire::test(RegisterSae::class)
        ->fillForm([
            'name' => 'Sénégal Incub Hub',
            'slug' => 'senegal-incub-hub',
            'email' => 'contact@senegal-incub.com',
            'types' => [$couveuse->id, $coworking->id],
            'vision' => '<p>Notre vision</p>',
            'mission' => '<p>Notre mission</p>',
            'objectives' => '<p>Nos objectifs</p>',
            'services' => '<p>Nos services</p>',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $sae = Sae::where('slug', 'senegal-incub-hub')->first();
    expect($sae)->not->toBeNull()
        ->and($sae->types()->count())->toBe(2)
        ->and($sae->types->pluck('slug')->toArray())->toContain('couveuse', 'espace-coworking');
});

test('le responsable de la SAE peut mettre a jour ses types dans le profil de la SAE', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Impact Space Bamako',
        'slug' => 'impact-space-bamako',
        'owner_id' => $user->id,
    ]);
    $user->saes()->attach($sae->id, ['role' => 'RESPONSABLE', 'status' => 'ACTIVE']);

    $this->actingAs($user);
    Filament::setTenant($sae);

    $typeCouveuse = SaeType::firstOrCreate(['slug' => 'couveuse'], ['name' => 'Couveuse d\'entreprises']);
    $typePepiniere = SaeType::firstOrCreate(['slug' => 'pepiniere'], ['name' => 'Pépinière d\'entreprises']);

    Livewire::test(EditSaeProfile::class)
        ->fillForm([
            'name' => 'Impact Space Bamako Modifié',
            'types' => [$typeCouveuse->id, $typePepiniere->id],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $sae->refresh();
    expect($sae->types()->count())->toBe(2)
        ->and($sae->types->pluck('slug')->toArray())->toContain('couveuse', 'pepiniere');
});

test('le widget de dashboard SAE affiche le nom et les types de la SAE', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Carthage Cowork & Hatch',
        'slug' => 'carthage-cowork',
        'owner_id' => $user->id,
    ]);
    $typeCoworking = SaeType::firstOrCreate(['slug' => 'espace-coworking'], ['name' => 'Espace de coworking']);
    $sae->types()->sync([$typeCoworking->id]);

    $this->actingAs($user);
    Filament::setTenant($sae);

    Livewire::test(SaeIdentityWidget::class)
        ->assertSuccessful()
        ->assertSee('Carthage Cowork & Hatch')
        ->assertSee('Espace de coworking');
});

test('le widget de dashboard Coach affiche la structure SAE et ses types', function () {
    $sae = Sae::create([
        'name' => 'Kigali Innovation City Hub',
        'slug' => 'kigali-innov-hub',
    ]);
    $typeIncubateur = SaeType::firstOrCreate(['slug' => 'incubateur'], ['name' => 'Incubateur d\'entreprises']);
    $typeFabLab = SaeType::firstOrCreate(['slug' => 'fablab-makerspace'], ['name' => 'FabLab / MakerSpace']);
    $sae->types()->sync([$typeIncubateur->id, $typeFabLab->id]);

    $coachUser = User::factory()->create();
    $coach = Coach::create([
        'user_id' => $coachUser->id,
        'sae_id' => $sae->id,
        'name' => 'Dr. Paul Kagame',
        'email' => 'paul@coach.com',
        'status' => 'ACTIF',
    ]);
    $coachUser->saes()->attach($sae->id, ['role' => 'COACH', 'status' => 'ACTIVE']);

    $this->actingAs($coachUser);
    Filament::setTenant($sae);

    Livewire::test(CoachSaeIdentityWidget::class)
        ->assertSuccessful()
        ->assertSee('Kigali Innovation City Hub')
        ->assertSee('Incubateur d\'entreprises')
        ->assertSee('FabLab / MakerSpace');
});

test('les pages publiques annuaire et detail affichent les types de la SAE', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Lomé Tech Hub',
        'slug' => 'lome-tech-hub',
        'owner_id' => $user->id,
        'status' => 'ACTIVE',
    ]);
    $type1 = SaeType::firstOrCreate(['slug' => 'espace-coworking'], ['name' => 'Espace de coworking']);
    $type2 = SaeType::firstOrCreate(['slug' => 'pepiniere'], ['name' => 'Pépinière d\'entreprises']);
    $sae->types()->sync([$type1->id, $type2->id]);

    // Annuaire
    $responseIndex = $this->get(route('public.structures'));
    $responseIndex->assertSuccessful();
    $responseIndex->assertSee('Lomé Tech Hub');
    $responseIndex->assertSee('Espace de coworking');
    $responseIndex->assertSee('Pépinière d\'entreprises');

    // Détail
    $responseShow = $this->get(route('public.structure.detail', $sae->id));
    $responseShow->assertSuccessful();
    $responseShow->assertSee('Lomé Tech Hub');
    $responseShow->assertSee('Espace de coworking');
    $responseShow->assertSee('Pépinière d\'entreprises');
    $responseShow->assertSee('Notre Vision');
    $responseShow->assertSee('Notre Mission');

    // Filtrage par type sur l'annuaire
    $responseFilter = $this->get(route('public.structures', ['type' => 'espace-coworking']));
    $responseFilter->assertSuccessful();
    $responseFilter->assertSee('Lomé Tech Hub');
});

test('la page de detail de la structure affiche distinctement la vision et la mission personnalisees', function () {
    $sae = Sae::create([
        'name' => 'Kigali Future Hub',
        'slug' => 'kigali-future-hub',
        'status' => 'ACTIVE',
        'vision' => 'Propulser 500 champions technologiques africains d ici 2030.',
        'mission' => 'Fournir un mentorat intensif et des laboratoires de prototypage de classe mondiale.',
        'objectives' => 'Créer 10000 emplois qualifiés pour la jeunesse.',
        'services' => 'Incubation résidentielle, FabLab IA, et accès direct aux fonds d amorçage.',
    ]);

    $response = $this->get(route('public.structure.detail', $sae->id));
    $response->assertSuccessful();
    $response->assertSee('Notre Vision');
    $response->assertSee('Propulser 500 champions technologiques africains d ici 2030.');
    $response->assertSee('Notre Mission');
    $response->assertSee('Fournir un mentorat intensif et des laboratoires de prototypage de classe mondiale.');
    $response->assertSee('Objectifs &amp; Piliers d\'Intervention', false);
    $response->assertSee('Créer 10000 emplois qualifiés pour la jeunesse.');
    $response->assertSee('Services &amp; Offre d\'Accompagnement', false);
    $response->assertSee('Incubation résidentielle, FabLab IA, et accès direct aux fonds d amorçage.');
});

test('la page d accueil et la fiche programme du portal affichent les types de la SAE', function () {
    $sae = Sae::create([
        'name' => 'Cotonou Startup Factory',
        'slug' => 'cotonou-startup-factory',
        'status' => 'ACTIVE',
    ]);
    $typeCouveuse = SaeType::firstOrCreate(['slug' => 'couveuse'], ['name' => 'Couveuse d\'entreprises']);
    $sae->types()->sync([$typeCouveuse->id]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme Emergence Bénin',
        'slug' => 'programme-emergence-benin',
        'status' => 'ACTIF',
    ]);

    // Page d'accueil
    $responseHome = $this->get('/');
    $responseHome->assertSuccessful();
    $responseHome->assertSee('Cotonou Startup Factory');
    $responseHome->assertSee('Couveuse d\'entreprises');

    // Fiche programme
    $responseProgram = $this->get(route('public.programme.detail', $program->id));
    $responseProgram->assertSuccessful();
    $responseProgram->assertSee('Cotonou Startup Factory');
    $responseProgram->assertSee('Couveuse d\'entreprises');
});

test('le logo de la SAE est affiche sur le portal et bascule sur les initiales par defaut si absent', function () {
    // SAE avec logo
    $saeWithLogo = Sae::create([
        'name' => 'Abidjan Cyber Hub',
        'slug' => 'abidjan-cyber-hub',
        'status' => 'ACTIVE',
        'logo' => 'https://example.com/logo-abidjan.png',
    ]);

    // SAE sans logo
    $saeWithoutLogo = Sae::create([
        'name' => 'Dakar Innovation Lab',
        'slug' => 'dakar-innovation-lab',
        'status' => 'ACTIVE',
        'logo' => null,
    ]);

    // Test sur l'annuaire des structures
    $responseIndex = $this->get(route('public.structures'));
    $responseIndex->assertSuccessful();
    // Le logo de l'un est présent
    $responseIndex->assertSee('https://example.com/logo-abidjan.png');
    // Les initiales de l'autre sont présentes (Da)
    $responseIndex->assertSee('Da');

    // Test sur la fiche détail avec logo
    $responseDetailWith = $this->get(route('public.structure.detail', $saeWithLogo->id));
    $responseDetailWith->assertSuccessful();
    $responseDetailWith->assertSee('https://example.com/logo-abidjan.png');

    // Test sur la fiche détail sans logo -> fallback initiales "Da"
    $responseDetailWithout = $this->get(route('public.structure.detail', $saeWithoutLogo->id));
    $responseDetailWithout->assertSuccessful();
    $responseDetailWithout->assertSee('Da');
});
