<?php

use App\Models\Opportunity;
use App\Models\Sae;
use App\Models\User;
use App\Services\OpportunityImportService;

test('opportunity import service imports opportunities from json file', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'SAE CUBE Lomé',
        'slug' => 'sae-cube-lome',
    ]);

    $tempJsonPath = tempnam(sys_get_temp_dir(), 'opp_').'.json';
    $sampleData = [
        [
            'title' => 'Bourse Climat Ouest-Africaine',
            'sectors' => ['Climat', 'Énergie'],
            'target_zones' => ['Togo', 'Bénin'],
            'details' => '<p>Financement de 15 000 USD pour projets écologiques.</p>',
            'learn_more_url' => 'https://example.com/climat',
            'apply_url' => 'https://example.com/climat/apply',
            'deadline' => '2026-12-15 18:00:00',
            'is_active' => true,
        ],
        [
            'title' => 'Concours EdTech Francophone',
            'sectors' => ['EdTech', 'Éducation'],
            'target_zones' => ['Panafricain'],
            'details' => '<p>Prix de 25 000 USD pour l\'éducation numérique.</p>',
            'deadline' => '2026-11-25 23:59:00',
            'is_active' => true,
        ],
    ];

    file_put_contents($tempJsonPath, json_encode($sampleData));

    $service = new OpportunityImportService;
    $result = $service->importFromFile($tempJsonPath, $sae);

    @unlink($tempJsonPath);

    expect($result['imported_count'])->toBe(2)
        ->and($result['skipped_count'])->toBe(0);

    $importedOpportunity = Opportunity::withoutGlobalScopes()
        ->where('title', 'Bourse Climat Ouest-Africaine')
        ->first();

    expect($importedOpportunity)->not->toBeNull()
        ->and($importedOpportunity->sae_id)->toBe($sae->id)
        ->and($importedOpportunity->sectors)->toBe(['Climat', 'Énergie'])
        ->and($importedOpportunity->target_zones)->toBe(['Togo', 'Bénin'])
        ->and($importedOpportunity->isOpen())->toBeTrue();
});

test('opportunity import service imports opportunities from csv file with delimiter detection', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Hub Innov Dakar',
        'slug' => 'hub-innov-dakar',
    ]);

    $tempCsvPath = tempnam(sys_get_temp_dir(), 'opp_').'.csv';
    $csvContent = implode("\n", [
        'titre;secteurs;zones;details;en_savoir_plus;postuler;deadline',
        'Appel FinTech UEMOA;FinTech, Banque;Sénégal, Côte d\'Ivoire;<p>Subvention 20k EUR</p>;https://fintech.org;https://fintech.org/apply;2026-12-31 23:59:00',
        'Challenge AgriTech Sahel;AgriTech;Mali, Niger;<p>Dotation 50k USD</p>;https://agri.org;https://agri.org/apply;2026-11-28 20:00:00',
    ]);

    file_put_contents($tempCsvPath, $csvContent);

    $service = new OpportunityImportService;
    $result = $service->importFromFile($tempCsvPath, $sae);

    @unlink($tempCsvPath);

    expect($result['imported_count'])->toBe(2)
        ->and($result['skipped_count'])->toBe(0);

    $fintechOpp = Opportunity::withoutGlobalScopes()
        ->where('title', 'Appel FinTech UEMOA')
        ->first();

    expect($fintechOpp)->not->toBeNull()
        ->and($fintechOpp->sectors)->toBe(['FinTech', 'Banque'])
        ->and($fintechOpp->target_zones)->toBe(['Sénégal', "Côte d'Ivoire"]);
});

test('opportunity import service gracefully handles rows with missing title', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'Test SAE Hub',
        'slug' => 'test-sae-hub',
    ]);

    $rows = [
        [
            'title' => 'Titre Valide',
            'details' => 'Description valide',
        ],
        [
            'title' => '', // Manquant
            'details' => 'Ligne sans titre',
        ],
    ];

    $service = new OpportunityImportService;
    $result = $service->importRows($rows, $sae->id);

    expect($result['imported_count'])->toBe(1)
        ->and($result['skipped_count'])->toBe(1)
        ->and($result['errors'])->toHaveCount(1)
        ->and($result['errors'][0])->toContain('titre');
});

test('artisan command opportunities:import executes cleanly', function () {
    $user = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $user->id,
        'name' => 'CUBE Tech Hub',
        'slug' => 'cube-tech-hub',
    ]);

    $this->artisan('opportunities:import', [
        '--sae' => $sae->id,
    ])->assertSuccessful();
});
