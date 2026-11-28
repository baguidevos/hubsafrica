<?php

use App\Enums\ApplicationStatus;
use App\Enums\CohortStatus;
use App\Enums\ProgramStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Pages\AnalyticsPage;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\SaeCountryBreakdownService;
use App\Support\CountryCatalog;
use Filament\Facades\Filament;
use Livewire\Livewire;

test('country catalog normalizes ISO codes, casing, and accents', function () {
    expect(CountryCatalog::normalize('CI'))->toBe("Côte d'Ivoire")
        ->and(CountryCatalog::normalize("cote d'ivoire"))->toBe("Côte d'Ivoire")
        ->and(CountryCatalog::normalize('Senegal'))->toBe('Sénégal')
        ->and(CountryCatalog::normalize('BJ'))->toBe('Bénin')
        ->and(CountryCatalog::normalize('   '))->toBeNull()
        ->and(CountryCatalog::normalize(null))->toBeNull();
});

test('country catalog extracts country from application answers keys', function () {
    expect(CountryCatalog::extractCountryFromAnswers(['pays' => 'cote d\'ivoire']))->toBe("Côte d'Ivoire")
        ->and(CountryCatalog::extractCountryFromAnswers(['country' => 'SN']))->toBe('Sénégal')
        ->and(CountryCatalog::extractCountryFromAnswers(['pays_de_residence' => 'Bénin']))->toBe('Bénin')
        ->and(CountryCatalog::extractCountryFromAnswers(['custom_pays_origine' => 'Togo']))->toBe('Togo')
        ->and(CountryCatalog::extractCountryFromAnswers(['sector' => 'AgriTech']))->toBeNull()
        ->and(CountryCatalog::extractCountryFromAnswers(null))->toBeNull();
});

test('sae country breakdown prioritizes entrepreneur country, falls back to application answers, and includes accounts without project', function () {
    $sae = Sae::create([
        'name' => 'SAE Afrique Test',
        'slug' => 'sae-afrique-test',
    ]);

    $programA = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Appel Innovation 2026',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 50,
    ]);

    $cohortA = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'name' => 'Cohorte 1',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 50,
    ]);

    $programB = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Appel Femmes Tech',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 30,
    ]);

    // 1. Entrepreneur with country on account + project + application (account country takes precedence over application answer)
    $ent1 = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Awa',
        'last_name' => 'Diallo',
        'email' => 'awa.diallo@example.com',
        'country' => 'Sénégal',
        'status' => 'ACTIVE',
    ]);
    Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $ent1->id,
        'name' => 'SolarFarm',
        'status' => ProjectStatus::ACTIF,
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programA->id,
        'cohort_id' => $cohortA->id,
        'entrepreneur_id' => $ent1->id,
        'status' => ApplicationStatus::SOUMISE,
        'answers' => ['pays' => 'Mali'],
    ]);

    // 2. Entrepreneur WITHOUT country on account, WITH application answer (fallback to application)
    $ent2 = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Kouassi',
        'last_name' => 'Yao',
        'email' => 'kouassi.yao@example.com',
        'country' => null,
        'status' => 'ACTIVE',
    ]);
    Application::create([
        'sae_id' => $sae->id,
        'program_id' => $programB->id,
        'entrepreneur_id' => $ent2->id,
        'status' => ApplicationStatus::SOUMISE,
        'answers' => ['pays_residence' => "cote d'ivoire"],
    ]);

    // 3. Entrepreneur WITHOUT project and WITHOUT application, WITH country on account
    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Mamadou',
        'last_name' => 'Ba',
        'email' => 'mamadou.ba@example.com',
        'country' => 'SN',
        'status' => 'ACTIVE',
    ]);

    // 4. Entrepreneur WITHOUT project, WITHOUT application, WITHOUT country
    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Inconnu',
        'last_name' => 'Test',
        'email' => 'inconnu.test@example.com',
        'country' => null,
        'status' => 'ACTIVE',
    ]);

    $service = app(SaeCountryBreakdownService::class);
    $breakdown = $service->buildForSae($sae);

    expect($breakdown['summary']['total_accounts'])->toBe(4)
        ->and($breakdown['summary']['accounts_with_application'])->toBe(2)
        ->and($breakdown['summary']['accounts_with_project'])->toBe(1)
        ->and($breakdown['summary']['accounts_without_project'])->toBe(3)
        ->and($breakdown['summary']['total_applications'])->toBe(2)
        ->and($breakdown['summary']['total_calls'])->toBe(2);

    $globalByCountry = collect($breakdown['global_by_country'])->keyBy('country');

    expect($globalByCountry->get('Sénégal'))->toMatchArray([
        'total_accounts' => 2,
        'with_application' => 1,
        'with_project' => 1,
        'without_project' => 1,
    ]);

    expect($globalByCountry->get("Côte d'Ivoire"))->toMatchArray([
        'total_accounts' => 1,
        'with_application' => 1,
        'with_project' => 0,
        'without_project' => 1,
    ]);

    expect($globalByCountry->get(CountryCatalog::UNKNOWN_LABEL))->toMatchArray([
        'total_accounts' => 1,
        'with_application' => 0,
        'with_project' => 0,
        'without_project' => 1,
    ]);

    $calls = collect($breakdown['by_call_and_country'])->keyBy('call_label');
    expect($calls->has('Appel Innovation 2026 — Cohorte 1'))->toBeTrue()
        ->and($calls->get('Appel Innovation 2026 — Cohorte 1')['countries'][0])->toMatchArray([
            'country' => 'Sénégal',
            'count' => 1,
        ])
        ->and($calls->get('Appel Femmes Tech')['countries'][0])->toMatchArray([
            'country' => "Côte d'Ivoire",
            'count' => 1,
        ]);

    // CSV streaming smoke check
    $response = $service->streamCsv($sae, $breakdown);
    ob_start();
    $response->sendContent();
    $csvContent = (string) ob_get_clean();

    expect($csvContent)->toContain('RÉPARTITION GLOBALE PAR PAYS')
        ->and($csvContent)->toContain('RÉPARTITION PAR APPEL')
        ->and($csvContent)->toContain('Sénégal')
        ->and($csvContent)->toContain("Côte d'Ivoire")
        ->and($csvContent)->toContain('Appel Innovation 2026 — Cohorte 1');
});

test('cohort registration requires country and saves normalized country on entrepreneur account', function () {
    $sae = Sae::create([
        'name' => 'SAE Dakar',
        'slug' => 'sae-dakar-country',
    ]);

    $program = Program::create([
        'sae_id' => $sae->id,
        'name' => 'Programme FinTech',
        'status' => ProgramStatus::ACTIF,
        'capacity' => 20,
    ]);

    $cohort = Cohort::create([
        'sae_id' => $sae->id,
        'program_id' => $program->id,
        'name' => 'Cohorte FinTech 2026',
        'status' => CohortStatus::OUVERTE,
        'capacity' => 20,
    ]);

    // Missing country fails validation
    $this->post(route('public.cohort.register', $cohort->slug), [
        'first_name' => 'Amina',
        'last_name' => 'Koné',
        'email' => 'amina.kone@example.com',
        'phone' => '+225 07 00 00 00',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertSessionHasErrors('country');

    // Valid country is normalized and saved
    $this->post(route('public.cohort.register', $cohort->slug), [
        'first_name' => 'Amina',
        'last_name' => 'Koné',
        'email' => 'amina.kone@example.com',
        'phone' => '+225 07 00 00 00',
        'country' => 'CI',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ])->assertRedirect();

    $entrepreneur = Entrepreneur::withoutGlobalScopes()->where('email', 'amina.kone@example.com')->first();
    expect($entrepreneur)->not->toBeNull()
        ->and($entrepreneur->country)->toBe("Côte d'Ivoire");
});

test('sae analytics page displays country breakdown and exports csv', function () {
    $sae = Sae::create([
        'name' => 'SAE Analytics Country',
        'slug' => 'sae-analytics-country',
    ]);

    $user = User::factory()->create();
    $user->saes()->attach($sae->id, ['role' => 'admin']);

    Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Ibrahima',
        'last_name' => 'Sarr',
        'email' => 'ibrahima.sarr@example.com',
        'country' => 'Sénégal',
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    Livewire::test(AnalyticsPage::class)
        ->assertSee('Répartition géographique par pays')
        ->assertSee('Sénégal')
        ->call('exportCountryBreakdownCsv')
        ->assertFileDownloaded();
});
