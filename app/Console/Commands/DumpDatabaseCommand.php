<?php

namespace App\Console\Commands;

use App\Services\DatabaseDumpService;
use Illuminate\Console\Command;
use Throwable;

class DumpDatabaseCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'db:dump
        {--connection= : Nom de la connexion de base de données à exporter}
        {--no-compress : Générer un fichier .sql non compressé}
        {--keep= : Nombre de sauvegardes récentes à conserver après le dump}';

    /**
     * @var string
     */
    protected $description = 'Crée un dump SQL de la base de données dans le stockage privé (storage/app/private/backups).';

    public function handle(DatabaseDumpService $dumpService): int
    {
        $connection = $this->option('connection');
        $connectionName = is_string($connection) && $connection !== '' ? $connection : null;
        $compress = ! (bool) $this->option('no-compress');
        $keepOption = $this->option('keep');

        try {
            $summary = $dumpService->createDump($connectionName, $compress);

            $this->info("Dump créé avec succès : {$summary['filename']}");
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['Fichier', $summary['filename']],
                    ['Emplacement', 'storage/app/private/'.$summary['relative_path']],
                    ['Pilote', $summary['driver']],
                    ['Base', $summary['database']],
                    ['Méthode', $summary['method']],
                    ['Taille', $summary['size_human']],
                    ['Compressé', $summary['compressed'] ? 'Oui (.sql.gz)' : 'Non (.sql)'],
                ]
            );

            if ($keepOption !== null && $keepOption !== '') {
                $keepCount = (int) $keepOption;
                $deletedCount = $dumpService->pruneOldBackups($keepCount);
                $this->line("Rétention appliquée : {$deletedCount} ancienne(s) sauvegarde(s) supprimée(s) (conservation des {$keepCount} plus récentes).");
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Échec du dump de la base de données : '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
