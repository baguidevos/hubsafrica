<?php

use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Filament\Entrepreneur\Resources\Applications\ApplicationResource as EntrepreneurApplicationResource;
use App\Filament\Entrepreneur\Resources\DataRoomDocuments\DataRoomDocumentResource as EntrepreneurDataRoomDocumentResource;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\DeliverableSubmissionResource as EntrepreneurDeliverableSubmissionResource;
use App\Filament\Entrepreneur\Resources\Projects\ProjectResource as EntrepreneurProjectResource;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;

test('deleting an entrepreneur cascades to their projects, detaches sae pivot, and deletes their user account', function () {
    $sae = Sae::create([
        'name' => 'SAE Sécurité',
        'slug' => 'sae-securite-'.uniqid(),
    ]);

    $candidateUser = User::factory()->create();
    $sae->members()->attach($candidateUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $candidateUser->id,
        'first_name' => 'Kouassi',
        'last_name' => 'Yao',
        'email' => $candidateUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Projet À Supprimer',
        'sector' => 'Agriculture',
        'status' => ProjectStatus::ACTIF,
    ]);

    $entrepreneur->delete();

    expect(Entrepreneur::withoutGlobalScopes()->find($entrepreneur->id))->toBeNull()
        ->and(Project::withoutGlobalScopes()->find($project->id))->toBeNull()
        ->and(User::find($candidateUser->id))->toBeNull()
        ->and(DB::table('sae_users')->where('user_id', $candidateUser->id)->exists())->toBeFalse();
});

test('deleting an entrepreneur preserves the user account if the user is also a sae manager', function () {
    $managerUser = User::factory()->create();
    $sae = Sae::create([
        'owner_id' => $managerUser->id,
        'name' => 'SAE Manager',
        'slug' => 'sae-manager-'.uniqid(),
    ]);
    $sae->members()->attach($managerUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $managerUser->id,
        'first_name' => 'Directeur',
        'last_name' => 'Test',
        'email' => $managerUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'Projet Test Manager',
        'sector' => 'Tech',
        'status' => ProjectStatus::ACTIF,
    ]);

    $entrepreneur->delete();

    expect(Project::withoutGlobalScopes()->find($project->id))->toBeNull()
        ->and(User::find($managerUser->id))->not->toBeNull()
        ->and(
            DB::table('sae_users')
                ->where('user_id', $managerUser->id)
                ->where('sae_id', $sae->id)
                ->where('role', 'ENTREPRENEUR')
                ->exists()
        )->toBeFalse();
});

test('a user without an entrepreneur profile cannot access the entrepreneur panel or see other entrepreneurs projects', function () {
    $sae = Sae::create([
        'name' => 'SAE Isolation',
        'slug' => 'sae-isolation-'.uniqid(),
    ]);

    $otherUser = User::factory()->create();
    $otherEntrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $otherUser->id,
        'first_name' => 'Aminata',
        'last_name' => 'Sow',
        'email' => $otherUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $otherProject = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $otherEntrepreneur->id,
        'name' => 'Projet Confidentiel',
        'sector' => 'Fintech',
        'status' => ProjectStatus::ACTIF,
    ]);

    $orphanUser = User::factory()->create();
    $sae->members()->attach($orphanUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);

    $panel = Filament::getPanel('entrepreneur');
    $this->actingAs($orphanUser);
    Filament::setCurrentPanel($panel);
    Filament::setTenant($sae);

    expect($orphanUser->canAccessPanel($panel))->toBeFalse()
        ->and($orphanUser->canAccessTenant($sae))->toBeFalse()
        ->and(EntrepreneurProjectResource::getEloquentQuery()->count())->toBe(0)
        ->and(EntrepreneurApplicationResource::getEloquentQuery()->count())->toBe(0)
        ->and(EntrepreneurDataRoomDocumentResource::getEloquentQuery()->count())->toBe(0)
        ->and(EntrepreneurDeliverableSubmissionResource::getEloquentQuery()->count())->toBe(0)
        ->and(EntrepreneurProjectResource::canView($otherProject))->toBeFalse()
        ->and(EntrepreneurProjectResource::canEdit($otherProject))->toBeFalse()
        ->and(EntrepreneurProjectResource::canDelete($otherProject))->toBeFalse();
});

test('an entrepreneur can only view, edit, and query their own project', function () {
    $sae = Sae::create([
        'name' => 'SAE Ownership',
        'slug' => 'sae-ownership-'.uniqid(),
    ]);

    $userA = User::factory()->create();
    $sae->members()->attach($userA->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);
    $entrepreneurA = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $userA->id,
        'first_name' => 'Alice',
        'last_name' => 'Konan',
        'email' => $userA->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $projectA = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneurA->id,
        'name' => 'Projet Alice',
        'sector' => 'Agriculture',
        'status' => ProjectStatus::ACTIF,
    ]);

    $userB = User::factory()->create();
    $sae->members()->attach($userB->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);
    $entrepreneurB = Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $userB->id,
        'first_name' => 'Brahima',
        'last_name' => 'Touré',
        'email' => $userB->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);
    $projectB = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneurB->id,
        'name' => 'Projet Brahima',
        'sector' => 'Énergie',
        'status' => ProjectStatus::ACTIF,
    ]);

    $this->actingAs($userA);
    Filament::setCurrentPanel(Filament::getPanel('entrepreneur'));
    Filament::setTenant($sae);

    expect(EntrepreneurProjectResource::getEloquentQuery()->pluck('id')->all())->toBe([$projectA->id])
        ->and(EntrepreneurProjectResource::canView($projectA))->toBeTrue()
        ->and(EntrepreneurProjectResource::canEdit($projectA))->toBeTrue()
        ->and(EntrepreneurProjectResource::canView($projectB))->toBeFalse()
        ->and(EntrepreneurProjectResource::canEdit($projectB))->toBeFalse()
        ->and(EntrepreneurProjectResource::canDelete($projectB))->toBeFalse();
});

test('cleanup migration removes orphaned entrepreneur pivots and accounts while preserving active users', function () {
    $sae = Sae::create([
        'name' => 'SAE Cleanup',
        'slug' => 'sae-cleanup-'.uniqid(),
    ]);

    // 1. Compte orphelin (pivot ENTREPRENEUR mais plus de ligne dans entrepreneurs)
    $orphanUser = User::factory()->create();
    $sae->members()->attach($orphanUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);

    // 2. Compte actif (possède toujours son profil Entrepreneur)
    $activeUser = User::factory()->create();
    $sae->members()->attach($activeUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);
    Entrepreneur::create([
        'sae_id' => $sae->id,
        'user_id' => $activeUser->id,
        'first_name' => 'Actif',
        'last_name' => 'Porteur',
        'email' => $activeUser->email,
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    // 3. Responsable SAE avec un pivot ENTREPRENEUR orphelin : pivot nettoyé mais compte conservé
    $managerUser = User::factory()->create();
    $sae->update(['owner_id' => $managerUser->id]);
    $sae->members()->attach($managerUser->id, ['role' => 'ENTREPRENEUR', 'status' => 'ACTIVE']);

    $migration = require database_path('migrations/2026_11_21_151017_cleanup_orphaned_entrepreneur_accounts_and_pivots.php');
    $migration->up();

    expect(User::find($orphanUser->id))->toBeNull()
        ->and(DB::table('sae_users')->where('user_id', $orphanUser->id)->exists())->toBeFalse()
        ->and(User::find($activeUser->id))->not->toBeNull()
        ->and(DB::table('sae_users')->where('user_id', $activeUser->id)->where('role', 'ENTREPRENEUR')->exists())->toBeTrue()
        ->and(User::find($managerUser->id))->not->toBeNull()
        ->and(DB::table('sae_users')->where('user_id', $managerUser->id)->where('role', 'ENTREPRENEUR')->exists())->toBeFalse();
});
