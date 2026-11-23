<?php

namespace App\Filament\Admin\Pages;

use App\Services\DatabaseDumpService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;
use UnitEnum;

class DatabaseBackupPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Sauvegardes BDD';

    protected static ?string $title = 'Sauvegardes & Dump de la Base de Données';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';

    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.admin.pages.database-backup-page';

    public bool $compress = true;

    public int $retentionCount = 10;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->isSuperAdmin();
    }

    /**
     * @return array{connection: string, driver: string, database: string, host: string}
     */
    public function getConnectionInfoProperty(): array
    {
        return app(DatabaseDumpService::class)->connectionSummary();
    }

    /**
     * @return array<int, array{
     *     filename: string,
     *     size: int,
     *     size_human: string,
     *     created_at: string,
     *     timestamp: int,
     *     compressed: bool
     * }>
     */
    public function getBackupsProperty(): array
    {
        return app(DatabaseDumpService::class)->listBackups();
    }

    public function getTotalBackupSizeProperty(): string
    {
        $dumpService = app(DatabaseDumpService::class);
        $totalBytes = array_sum(array_column($this->getBackupsProperty(), 'size'));

        return $dumpService->formatBytes((int) $totalBytes);
    }

    public function createDump(DatabaseDumpService $dumpService): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        try {
            $summary = $dumpService->createDump(compress: $this->compress);

            Notification::make()
                ->title('Dump de la base de données créé')
                ->body("Fichier « {$summary['filename']} » ({$summary['size_human']}) généré via {$summary['method']}.")
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Erreur lors de la création du dump')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteBackup(string $filename, DatabaseDumpService $dumpService): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        try {
            $dumpService->deleteBackup($filename);

            Notification::make()
                ->title('Sauvegarde supprimée')
                ->body("Le fichier « {$filename} » a été supprimé du stockage privé.")
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Suppression impossible')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function pruneBackups(DatabaseDumpService $dumpService): void
    {
        if (! auth()->user()?->isSuperAdmin()) {
            abort(403);
        }

        $keep = max(1, $this->retentionCount);
        $deletedCount = $dumpService->pruneOldBackups($keep);

        Notification::make()
            ->title('Nettoyage terminé')
            ->body("{$deletedCount} ancienne(s) sauvegarde(s) supprimée(s). Les {$keep} plus récentes sont conservées.")
            ->success()
            ->send();
    }
}
