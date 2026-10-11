<?php

use App\Enums\SaeOriginType;
use App\Filament\Admin\Resources\Saes\Pages\EditSae;
use App\Filament\Sae\Pages\Tenancy\EditSaeProfile;
use App\Filament\Sae\Pages\Tenancy\RegisterSae;
use App\Models\Role;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('le modele Sae gère correctement les champs d origine et de provenance', function () {
    $saeUniv = Sae::create([
        'name' => 'Incubateur Universitaire de Lomé',
        'slug' => 'incubateur-univ-lome',
        'origin_type' => SaeOriginType::UNIVERSITAIRE,
        'university_name' => 'Université de Lomé',
        'country' => 'Togo',
        'city' => 'Lomé',
    ]);

    expect($saeUniv->origin_type)->toBe(SaeOriginType::UNIVERSITAIRE)
        ->and($saeUniv->isUniversity())->toBeTrue()
        ->and($saeUniv->isIndependent())->toBeFalse()
        ->and($saeUniv->university_name)->toBe('Université de Lomé')
        ->and($saeUniv->provenance_label)->toBe('Lomé, Togo');

    $saeIndep = Sae::create([
        'name' => 'Tech Hub Dakar',
        'slug' => 'tech-hub-dakar',
        'origin_type' => SaeOriginType::INDEPENDANTE,
        'country' => 'Sénégal',
        'city' => 'Dakar',
    ]);

    expect($saeIndep->origin_type)->toBe(SaeOriginType::INDEPENDANTE)
        ->and($saeIndep->isUniversity())->toBeFalse()
        ->and($saeIndep->isIndependent())->toBeTrue()
        ->and($saeIndep->provenance_label)->toBe('Dakar, Sénégal');
});

test('l onboarding RegisterSae permet de choisir si la structure est universitaire ou independante avec provenance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(RegisterSae::class)
        ->fillForm([
            'name' => 'Centre d Innovation 2iE',
            'slug' => 'centre-innovation-2ie',
            'origin_type' => SaeOriginType::UNIVERSITAIRE->value,
            'university_name' => 'Institut International 2iE Ouagadougou',
            'country' => 'Burkina Faso',
            'city' => 'Ouagadougou',
            'email' => 'incubateur@2ie-edu.org',
            'phone' => '+226 25 49 28 00',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $createdSae = Sae::where('slug', 'centre-innovation-2ie')->first();
    expect($createdSae)->not->toBeNull()
        ->and($createdSae->origin_type)->toBe(SaeOriginType::UNIVERSITAIRE)
        ->and($createdSae->university_name)->toBe('Institut International 2iE Ouagadougou')
        ->and($createdSae->country)->toBe('Burkina Faso')
        ->and($createdSae->city)->toBe('Ouagadougou')
        ->and($createdSae->isUniversity())->toBeTrue();
});

test('l onboarding RegisterSae enregistre correctement une structure indépendante', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test(RegisterSae::class)
        ->fillForm([
            'name' => 'WoeLab Lomé',
            'slug' => 'woelab-lome',
            'origin_type' => SaeOriginType::INDEPENDANTE->value,
            'country' => 'Togo',
            'city' => 'Lomé',
            'email' => 'contact@woelab.tg',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $createdSae = Sae::where('slug', 'woelab-lome')->first();
    expect($createdSae)->not->toBeNull()
        ->and($createdSae->origin_type)->toBe(SaeOriginType::INDEPENDANTE)
        ->and($createdSae->isIndependent())->toBeTrue()
        ->and($createdSae->university_name)->toBeNull();
});

test('le profil de la structure EditSaeProfile permet de modifier l origine et la provenance', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'name' => 'Innov Space Cotonou',
        'slug' => 'innov-space-cotonou',
        'owner_id' => $user->id,
        'origin_type' => SaeOriginType::INDEPENDANTE,
    ]);
    $user->saes()->attach($sae->id, ['role' => 'RESPONSABLE', 'status' => 'ACTIVE']);

    $this->actingAs($user);
    Filament::setTenant($sae);

    Livewire::test(EditSaeProfile::class)
        ->fillForm([
            'name' => 'Pôle Entrepreneuriat UAC',
            'origin_type' => SaeOriginType::UNIVERSITAIRE->value,
            'university_name' => 'Université d Abomey-Calavi',
            'country' => 'Bénin',
            'city' => 'Abomey-Calavi',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $sae->refresh();
    expect($sae->origin_type)->toBe(SaeOriginType::UNIVERSITAIRE)
        ->and($sae->university_name)->toBe('Université d Abomey-Calavi')
        ->and($sae->country)->toBe('Bénin')
        ->and($sae->city)->toBe('Abomey-Calavi');
});

test('l administration globale permet d editer la provenance et l origine de la SAE', function () {
    setPermissionsTeamId(0);
    $adminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
    $admin = User::factory()->create();
    $admin->assignRole($adminRole);

    $sae = Sae::create([
        'name' => 'Yabatech Hub',
        'slug' => 'yabatech-hub',
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($admin);
    Filament::setCurrentPanel(Filament::getPanel('admin'));

    Livewire::test(EditSae::class, ['record' => $sae->id])
        ->fillForm([
            'origin_type' => SaeOriginType::UNIVERSITAIRE->value,
            'university_name' => 'Yaba College of Technology',
            'country' => 'Nigéria',
            'city' => 'Lagos',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $sae->refresh();
    expect($sae->origin_type)->toBe(SaeOriginType::UNIVERSITAIRE)
        ->and($sae->university_name)->toBe('Yaba College of Technology')
        ->and($sae->country)->toBe('Nigéria')
        ->and($sae->city)->toBe('Lagos');
});

test('le front affiche le badge universitaire et la provenance sur la fiche et l annuaire', function () {
    $sae = Sae::create([
        'name' => 'Incubateur UCAD Dakar',
        'slug' => 'incubateur-ucad-dakar',
        'status' => 'ACTIVE',
        'origin_type' => SaeOriginType::UNIVERSITAIRE,
        'university_name' => 'Université Cheikh Anta Diop',
        'country' => 'Sénégal',
        'city' => 'Dakar',
    ]);

    // Annuaire
    $responseIndex = $this->get(route('public.structures'));
    $responseIndex->assertSuccessful();
    $responseIndex->assertSee('Incubateur UCAD Dakar');
    $responseIndex->assertSee('Universitaire');
    $responseIndex->assertSee('Dakar, Sénégal');

    // Fiche détail
    $responseShow = $this->get(route('public.structure.detail', $sae->id));
    $responseShow->assertSuccessful();
    $responseShow->assertSee('Incubateur UCAD Dakar');
    $responseShow->assertSee('Universitaire — Université Cheikh Anta Diop');
    $responseShow->assertSee('Université Cheikh Anta Diop');
    $responseShow->assertSee('Dakar, Sénégal');
});
