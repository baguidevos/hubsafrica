<?php

use App\Enums\AuditAction;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Filament\Sae\Resources\AuditLogs\AuditLogResource as SaeAuditLogResource;
use App\Filament\Sae\Resources\AuditLogs\Pages\ListAuditLogs as SaeListAuditLogs;
use App\Models\AuditLog;
use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use App\Services\AuditLogger;
use Filament\Facades\Filament;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

test('formatModelChanges builds a readable diff and ignores sensitive or timestamp fields', function () {
    $summary = AuditLogger::formatModelChanges(
        changes: [
            'name' => 'Terroir Plus V2',
            'status' => ProjectStatus::DIPLOME,
            'password' => 'secret-hash',
            'remember_token' => 'tok',
            'updated_at' => '2026-11-21 14:00:00',
        ],
        original: [
            'name' => 'Terroir Plus',
            'status' => ProjectStatus::ACTIF,
            'password' => 'old-hash',
            'remember_token' => 'old-tok',
            'updated_at' => '2026-11-21 13:00:00',
        ],
    );

    expect($summary)->toBe('name: Terroir Plus -> Terroir Plus V2 | status: ACTIF -> DIPLOME');
});

test('formatModelChanges returns null when only ignored attributes changed', function () {
    $summary = AuditLogger::formatModelChanges(
        changes: [
            'password' => 'new-hash',
            'updated_at' => '2026-11-21 14:00:00',
        ],
        original: [
            'password' => 'old-hash',
            'updated_at' => '2026-11-21 13:00:00',
        ],
    );

    expect($summary)->toBeNull();
});

test('model create, update, and delete actions are automatically recorded in audit_logs', function () {
    $actor = User::factory()->create(['name' => 'Mariam Koné']);
    $this->actingAs($actor);

    $sae = Sae::create([
        'name' => 'SAE Audit Test',
        'slug' => 'sae-audit-test',
    ]);

    $entrepreneur = Entrepreneur::create([
        'sae_id' => $sae->id,
        'first_name' => 'Kouadio',
        'last_name' => 'Yao',
        'email' => 'kouadio.yao@example.com',
        'status' => EntrepreneurStatus::ACTIVE,
    ]);

    $project = Project::create([
        'sae_id' => $sae->id,
        'entrepreneur_id' => $entrepreneur->id,
        'name' => 'AgriDaloa',
        'sector' => 'Agriculture',
        'status' => ProjectStatus::ACTIF,
    ]);

    $createLog = AuditLog::withoutGlobalScopes()
        ->where('entity', 'Project')
        ->where('entity_id', (string) $project->id)
        ->where('action', AuditAction::CREATE->value)
        ->first();

    expect($createLog)->not->toBeNull();
    expect($createLog->user_id)->toBe($actor->id);
    expect($createLog->user_name)->toBe('Mariam Koné');
    expect($createLog->sae_id)->toBe($sae->id);
    expect($createLog->details)->toContain('AgriDaloa');

    $project->update(['name' => 'AgriDaloa Plus']);

    $updateLog = AuditLog::withoutGlobalScopes()
        ->where('entity', 'Project')
        ->where('entity_id', (string) $project->id)
        ->where('action', AuditAction::UPDATE->value)
        ->first();

    expect($updateLog)->not->toBeNull();
    expect($updateLog->details)->toContain('name: AgriDaloa -> AgriDaloa Plus');

    $projectId = (string) $project->id;
    $project->delete();

    $deleteLog = AuditLog::withoutGlobalScopes()
        ->where('entity', 'Project')
        ->where('entity_id', $projectId)
        ->where('action', AuditAction::DELETE->value)
        ->first();

    expect($deleteLog)->not->toBeNull();
    expect($deleteLog->details)->toContain('AgriDaloa Plus');
});

test('withoutAuditing temporarily disables automatic audit logging', function () {
    $sae = AuditLogger::withoutAuditing(fn () => Sae::create([
        'name' => 'SAE Silent',
        'slug' => 'sae-silent',
    ]));

    expect(
        AuditLog::withoutGlobalScopes()
            ->where('entity', 'Sae')
            ->where('entity_id', (string) $sae->id)
            ->exists()
    )->toBeFalse();

    expect(AuditLogger::isEnabled())->toBeTrue();
});

test('authentication events are recorded in audit_logs', function () {
    $user = User::factory()->create([
        'name' => 'Saliou Diop',
        'email' => 'saliou.diop@example.com',
    ]);

    Event::dispatch(new Login('web', $user, false));
    Event::dispatch(new PasswordReset($user));
    Event::dispatch(new Logout('web', $user));

    $actions = AuditLog::withoutGlobalScopes()
        ->where('entity', 'User')
        ->where('entity_id', (string) $user->id)
        ->whereIn('action', [
            AuditAction::LOGIN->value,
            AuditAction::PASSWORD_CHANGE->value,
            AuditAction::LOGOUT->value,
        ])
        ->pluck('action')
        ->all();

    expect($actions)->toContain(AuditAction::LOGIN->value);
    expect($actions)->toContain(AuditAction::PASSWORD_CHANGE->value);
    expect($actions)->toContain(AuditAction::LOGOUT->value);
});

test('sae audit log resource is read-only and lists recorded actions', function () {
    $user = User::factory()->create(['name' => 'Admin SAE']);
    $sae = Sae::create([
        'name' => 'SAE Panel Audit',
        'slug' => 'sae-panel-audit',
    ]);

    $this->actingAs($user);
    Filament::setCurrentPanel(Filament::getPanel('sae'));
    Filament::setTenant($sae);

    $log = AuditLogger::record(
        action: AuditAction::UPDATE_STATUS,
        entity: 'Application',
        entityId: 42,
        details: 'Statut passé à ACCEPTEE',
        saeId: $sae->id,
        user: $user,
    );

    expect(SaeAuditLogResource::canCreate())->toBeFalse();
    expect(SaeAuditLogResource::canEdit($log))->toBeFalse();
    expect(SaeAuditLogResource::canDelete($log))->toBeFalse();
    expect(SaeAuditLogResource::canDeleteAny())->toBeFalse();

    Livewire::test(SaeListAuditLogs::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$log]);
});
