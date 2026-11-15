<?php

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use App\Enums\AccompanimentToolPaymentStatus;
use App\Enums\AccompanimentToolRequestStatus;
use App\Models\AccompanimentTool;
use App\Models\AccompanimentToolAccessRequest;
use App\Models\AccompanimentToolPayment;
use App\Models\Sae;
use App\Models\User;
use App\Support\AccompanimentToolAccessPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('accompaniment tool access policy enforces all four access levels purely', function () {
    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::FREE,
        isPublished: false,
        isAuthenticated: true,
    )->code)->toBe('TOOL_UNPUBLISHED');

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::FREE,
        isPublished: true,
        isAuthenticated: false,
    )->canAccess)->toBeTrue();

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::ACCOUNT_REQUIRED,
        isPublished: true,
        isAuthenticated: false,
    )->code)->toBe('AUTHENTICATION_REQUIRED');

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::ACCOUNT_REQUIRED,
        isPublished: true,
        isAuthenticated: true,
    )->canAccess)->toBeTrue();

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::ON_REQUEST,
        isPublished: true,
        isAuthenticated: true,
        requestStatus: null,
    )->code)->toBe('REQUEST_REQUIRED');

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::ON_REQUEST,
        isPublished: true,
        isAuthenticated: true,
        requestStatus: AccompanimentToolRequestStatus::PENDING,
    )->code)->toBe('REQUEST_PENDING');

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::ON_REQUEST,
        isPublished: true,
        isAuthenticated: true,
        requestStatus: AccompanimentToolRequestStatus::APPROVED,
    )->canAccess)->toBeTrue();

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::PAID,
        isPublished: true,
        isAuthenticated: true,
        hasPaidAccess: false,
    )->code)->toBe('PAYMENT_REQUIRED');

    expect(AccompanimentToolAccessPolicy::evaluate(
        AccompanimentToolAccessLevel::PAID,
        isPublished: true,
        isAuthenticated: true,
        hasPaidAccess: true,
    )->canAccess)->toBeTrue();
});

test('public outils page renders and free document and drive link are accessible without account', function () {
    Storage::fake('local');
    Storage::disk('local')->put('accompaniment-tools/bmc-template.pdf', 'pdf-content');

    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Outils Libres',
        'slug' => 'sae-outils-libres-'.uniqid(),
    ]);

    $docTool = AccompanimentTool::create([
        'sae_id' => $sae->id,
        'title' => 'Business Model Canvas PDF',
        'format' => AccompanimentToolFormat::DOCUMENT,
        'file_path' => 'accompaniment-tools/bmc-template.pdf',
        'original_filename' => 'bmc-template.pdf',
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => true,
    ]);

    $driveTool = AccompanimentTool::create([
        'sae_id' => $sae->id,
        'title' => 'Dossier Modèles Drive',
        'format' => AccompanimentToolFormat::DRIVE_LINK,
        'drive_url' => 'https://drive.google.com/drive/folders/test-folder',
        'access_level' => AccompanimentToolAccessLevel::FREE,
        'is_published' => true,
    ]);

    $this->get(route('public.outils'))
        ->assertOk()
        ->assertSee('Business Model Canvas PDF')
        ->assertSee('Dossier Modèles Drive');

    $this->get(route('public.outils.access', ['slug' => $docTool->slug]))
        ->assertOk()
        ->assertDownload('bmc-template.pdf');

    $this->get(route('public.outils.access', ['slug' => $driveTool->slug]))
        ->assertRedirect('https://drive.google.com/drive/folders/test-folder');
});

test('account required tool blocks guests and allows authenticated users', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Compte Requis',
        'slug' => 'sae-compte-requis-'.uniqid(),
    ]);

    $tool = AccompanimentTool::create([
        'sae_id' => $sae->id,
        'title' => 'Checklist Levée de Fonds',
        'format' => AccompanimentToolFormat::DRIVE_LINK,
        'drive_url' => 'https://drive.google.com/file/d/checklist',
        'access_level' => AccompanimentToolAccessLevel::ACCOUNT_REQUIRED,
        'is_published' => true,
    ]);

    $this->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect(route('public.rejoindre'));

    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect('https://drive.google.com/file/d/checklist');
});

test('on request tool requires sae approval before granting access', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Sur Demande',
        'slug' => 'sae-sur-demande-'.uniqid(),
    ]);

    $tool = AccompanimentTool::create([
        'sae_id' => $sae->id,
        'title' => 'Kit Modélisation Financière',
        'format' => AccompanimentToolFormat::DRIVE_LINK,
        'drive_url' => 'https://drive.google.com/file/d/fin-kit',
        'access_level' => AccompanimentToolAccessLevel::ON_REQUEST,
        'is_published' => true,
    ]);

    $entrepreneur = User::factory()->create();

    $this->actingAs($entrepreneur)
        ->post(route('public.outils.request', ['slug' => $tool->slug]), [
            'request_message' => 'Nous préparons notre dossier d\'amorçage.',
        ])
        ->assertRedirect(route('public.outils'));

    $accessRequest = AccompanimentToolAccessRequest::where('accompaniment_tool_id', $tool->id)
        ->where('user_id', $entrepreneur->id)
        ->first();

    expect($accessRequest)->not->toBeNull()
        ->and($accessRequest->status)->toBe(AccompanimentToolRequestStatus::PENDING);

    // Accès encore refusé tant que non approuvé
    $this->actingAs($entrepreneur)
        ->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect(route('public.outils'));

    // Approbation par la SAE
    $accessRequest->update([
        'status' => AccompanimentToolRequestStatus::APPROVED,
        'reviewed_by_id' => $owner->id,
        'reviewed_at' => now(),
    ]);

    $this->actingAs($entrepreneur)
        ->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect('https://drive.google.com/file/d/fin-kit');
});

test('paid tool requires payment and unlocks permanently after sandbox callback', function () {
    $owner = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $owner->id,
        'name' => 'SAE Payant',
        'slug' => 'sae-payant-'.uniqid(),
    ]);

    $tool = AccompanimentTool::create([
        'sae_id' => $sae->id,
        'title' => 'Pack Juridique Pacte d\'Associés',
        'format' => AccompanimentToolFormat::DRIVE_LINK,
        'drive_url' => 'https://drive.google.com/file/d/pack-juridique',
        'access_level' => AccompanimentToolAccessLevel::PAID,
        'price' => 15000,
        'currency' => 'XOF',
        'is_published' => true,
    ]);

    $buyer = User::factory()->create();

    // Bloqué avant paiement
    $this->actingAs($buyer)
        ->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect(route('public.outils'));

    // Lancement du paiement (mode sandbox)
    $response = $this->actingAs($buyer)
        ->post(route('public.outils.pay', ['slug' => $tool->slug]));

    $payment = AccompanimentToolPayment::where('accompaniment_tool_id', $tool->id)
        ->where('user_id', $buyer->id)
        ->first();

    expect($payment)->not->toBeNull()
        ->and($payment->status)->toBe(AccompanimentToolPaymentStatus::PENDING);

    $response->assertRedirect();
    $callbackUrl = $response->headers->get('Location');

    // Exécution du callback sandbox
    $this->actingAs($buyer)
        ->get($callbackUrl)
        ->assertRedirect(route('public.outils'));

    $payment->refresh();
    expect($payment->status)->toBe(AccompanimentToolPaymentStatus::PAID);

    // Accès débloqué après paiement
    $this->actingAs($buyer)
        ->get(route('public.outils.access', ['slug' => $tool->slug]))
        ->assertRedirect('https://drive.google.com/file/d/pack-juridique');
});
