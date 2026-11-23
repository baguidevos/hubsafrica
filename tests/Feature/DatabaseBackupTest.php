<?php

use App\Filament\Admin\Pages\DatabaseBackupPage;
use App\Models\User;
use App\Services\DatabaseDumpService;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
});

test('service validates backup filenames and rejects path traversal', function () {
    $service = app(DatabaseDumpService::class);

    expect($service->isValidBackupFilename('db-mysql-prod-2026-11-23_120000.sql.gz'))->toBeTrue()
        ->and($service->isValidBackupFilename('db-sqlite-memory-2026-11-23_120000.sql'))->toBeTrue()
        ->and($service->isValidBackupFilename('../.env'))->toBeFalse()
        ->and($service->isValidBackupFilename('backups/dump.sql'))->toBeFalse()
        ->and($service->isValidBackupFilename('dump.txt'))->toBeFalse()
        ->and($service->isValidBackupFilename(''))->toBeFalse();

    expect($service->formatBytes(512))->toBe('512 o')
        ->and($service->formatBytes(2048))->toBe('2,00 Ko')
        ->and($service->sanitizeDatabaseName(':memory:'))->toBe('memory');
});

test('service creates an uncompressed sql dump with schema and rows', function () {
    $user = User::factory()->create([
        'name' => 'Backup Verification User',
        'email' => 'backup-check@hubsafrica.org',
    ]);

    $service = app(DatabaseDumpService::class);
    $dump = $service->createDump(compress: false);

    expect($dump['compressed'])->toBeFalse()
        ->and($dump['filename'])->toEndWith('.sql')
        ->and($dump['size'])->toBeGreaterThan(0);

    $absolutePath = $service->resolveBackupPath($dump['filename']);
    $sqlContent = file_get_contents($absolutePath);

    expect($sqlContent)->toBeString()
        ->toContain('CREATE TABLE')
        ->toContain('INSERT INTO')
        ->toContain($user->email);
});

test('service creates a gzip compressed sql dump that decompresses cleanly', function () {
    User::factory()->create(['email' => 'gzip-check@hubsafrica.org']);

    $service = app(DatabaseDumpService::class);
    $dump = $service->createDump(compress: true);

    expect($dump['compressed'])->toBeTrue()
        ->and($dump['filename'])->toEndWith('.sql.gz')
        ->and($dump['size'])->toBeGreaterThan(0);

    $absolutePath = $service->resolveBackupPath($dump['filename']);
    $decompressed = gzdecode((string) file_get_contents($absolutePath));

    expect($decompressed)->toBeString()
        ->toContain('CREATE TABLE')
        ->toContain('gzip-check@hubsafrica.org');
});

test('service lists, deletes, and prunes old backups according to retention', function () {
    $disk = Storage::disk('local');
    $disk->put('backups/db-mysql-prod-2026-01-01_000001.sql.gz', 'old-1');
    $disk->put('backups/db-mysql-prod-2026-01-02_000001.sql.gz', 'old-2');
    $disk->put('backups/db-mysql-prod-2026-01-03_000001.sql.gz', 'newest');
    $disk->put('backups/ignored.txt', 'not-a-backup');

    touch($disk->path('backups/db-mysql-prod-2026-01-01_000001.sql.gz'), now()->subDays(3)->timestamp);
    touch($disk->path('backups/db-mysql-prod-2026-01-02_000001.sql.gz'), now()->subDays(2)->timestamp);
    touch($disk->path('backups/db-mysql-prod-2026-01-03_000001.sql.gz'), now()->subDay()->timestamp);

    $service = app(DatabaseDumpService::class);
    $backups = $service->listBackups();

    expect($backups)->toHaveCount(3)
        ->and($backups[0]['filename'])->toBe('db-mysql-prod-2026-01-03_000001.sql.gz')
        ->and($backups[2]['filename'])->toBe('db-mysql-prod-2026-01-01_000001.sql.gz');

    $deleted = $service->pruneOldBackups(keep: 2);
    expect($deleted)->toBe(1)
        ->and($service->listBackups())->toHaveCount(2);

    $service->deleteBackup('db-mysql-prod-2026-01-02_000001.sql.gz');
    expect($service->listBackups())->toHaveCount(1);
});

test('artisan db:dump command generates a backup and applies keep retention', function () {
    Storage::disk('local')->put('backups/db-sqlite-old-2026-01-01_000001.sql.gz', 'old');
    touch(Storage::disk('local')->path('backups/db-sqlite-old-2026-01-01_000001.sql.gz'), now()->subWeek()->timestamp);

    $this->artisan('db:dump', ['--no-compress' => true, '--keep' => 1])
        ->expectsOutputToContain('Dump créé avec succès')
        ->expectsOutputToContain('Rétention appliquée : 1 ancienne(s) sauvegarde(s) supprimée(s)')
        ->assertSuccessful();

    $backups = app(DatabaseDumpService::class)->listBackups();
    expect($backups)->toHaveCount(1)
        ->and($backups[0]['compressed'])->toBeFalse();
});

test('super admin can access backup page, create, download, and delete a dump', function () {
    $superAdmin = User::factory()->create([
        'email' => 'admin@hubsafrica.org',
    ]);

    $this->actingAs($superAdmin)
        ->get('/hubs-hq/database-backup-page')
        ->assertOk()
        ->assertSee('Générer un dump de production');

    Livewire::actingAs($superAdmin)
        ->test(DatabaseBackupPage::class)
        ->assertSuccessful()
        ->call('createDump')
        ->assertNotified('Dump de la base de données créé');

    $backups = app(DatabaseDumpService::class)->listBackups();
    expect($backups)->toHaveCount(1);

    $filename = $backups[0]['filename'];

    $downloadResponse = $this->actingAs($superAdmin)
        ->get(route('admin.database-backups.download', ['filename' => $filename]));

    $downloadResponse->assertOk()
        ->assertDownload($filename);

    Livewire::actingAs($superAdmin)
        ->test(DatabaseBackupPage::class)
        ->call('deleteBackup', $filename)
        ->assertNotified('Sauvegarde supprimée');

    expect(app(DatabaseDumpService::class)->listBackups())->toBeEmpty();
});

test('non-super admin and guests cannot access backup page or download dumps', function () {
    $regularUser = User::factory()->create([
        'email' => 'regular-user@hubsafrica.org',
    ]);

    Storage::disk('local')->put('backups/db-mysql-prod-2026-11-23_120000.sql.gz', 'secret-sql');

    // Guest redirected to login
    $this->get(route('admin.database-backups.download', ['filename' => 'db-mysql-prod-2026-11-23_120000.sql.gz']))
        ->assertRedirect();

    // Non-super admin forbidden
    $this->actingAs($regularUser)
        ->get(route('admin.database-backups.download', ['filename' => 'db-mysql-prod-2026-11-23_120000.sql.gz']))
        ->assertForbidden();

    $this->actingAs($regularUser)
        ->get('/hubs-hq/database-backup-page')
        ->assertForbidden();
});
