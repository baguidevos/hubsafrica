<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PDO;
use RuntimeException;

class DatabaseDumpService
{
    private const BACKUP_DIRECTORY = 'backups';

    private const INSERT_BATCH_SIZE = 200;

    /**
     * Retourne les informations non sensibles de la connexion cible.
     *
     * @return array{connection: string, driver: string, database: string, host: string}
     */
    public function connectionSummary(?string $connection = null): array
    {
        $connectionName = $connection ?: (string) config('database.default');
        /** @var array<string, mixed> $connectionConfig */
        $connectionConfig = config("database.connections.{$connectionName}", []);

        $driver = (string) ($connectionConfig['driver'] ?? 'unknown');
        $database = (string) ($connectionConfig['database'] ?? 'unknown');
        $host = (string) ($connectionConfig['host'] ?? ($driver === 'sqlite' ? 'local' : '127.0.0.1'));

        return [
            'connection' => $connectionName,
            'driver' => $driver,
            'database' => $driver === 'sqlite' ? basename($database) : $database,
            'host' => $host,
        ];
    }

    /**
     * Crée un dump SQL (compressé ou non) de la base de données sur le disque privé.
     *
     * @return array{
     *     filename: string,
     *     relative_path: string,
     *     size: int,
     *     size_human: string,
     *     driver: string,
     *     database: string,
     *     compressed: bool,
     *     method: string,
     *     created_at: string
     * }
     */
    public function createDump(?string $connection = null, bool $compress = true): array
    {
        $connectionName = $connection ?: (string) config('database.default');
        /** @var array<string, mixed>|null $connectionConfig */
        $connectionConfig = config("database.connections.{$connectionName}");

        if (! is_array($connectionConfig)) {
            throw new InvalidArgumentException("DB_CONNECTION_NOT_FOUND: La connexion « {$connectionName} » n'existe pas.");
        }

        $driver = (string) ($connectionConfig['driver'] ?? '');
        $databaseName = $this->sanitizeDatabaseName((string) ($connectionConfig['database'] ?? $connectionName));
        $useCompression = $compress && function_exists('gzopen');

        $disk = Storage::disk('local');
        $disk->makeDirectory(self::BACKUP_DIRECTORY);

        $timestamp = now()->format('Y-m-d_His');
        $extension = $useCompression ? 'sql.gz' : 'sql';
        $filename = "db-{$driver}-{$databaseName}-{$timestamp}.{$extension}";
        $relativePath = self::BACKUP_DIRECTORY.'/'.$filename;
        $absolutePath = $disk->path($relativePath);

        $tempSqlPath = $useCompression ? $disk->path(self::BACKUP_DIRECTORY.'/tmp-'.Str::uuid().'.sql') : $absolutePath;

        try {
            $method = match ($driver) {
                'mysql', 'mariadb' => $this->dumpMySql($connectionName, $connectionConfig, $tempSqlPath),
                'pgsql' => $this->dumpPostgres($connectionName, $connectionConfig, $tempSqlPath),
                'sqlite' => $this->dumpSqliteViaPdo($connectionName, $tempSqlPath),
                default => throw new RuntimeException("DB_DRIVER_UNSUPPORTED: Le pilote « {$driver} » n'est pas pris en charge pour le dump."),
            };

            if ($useCompression) {
                $this->compressFile($tempSqlPath, $absolutePath);
                @unlink($tempSqlPath);
            }
        } catch (\Throwable $exception) {
            @unlink($tempSqlPath);
            @unlink($absolutePath);

            throw $exception;
        }

        clearstatcache(true, $absolutePath);
        $size = is_file($absolutePath) ? (int) filesize($absolutePath) : 0;

        return [
            'filename' => $filename,
            'relative_path' => $relativePath,
            'size' => $size,
            'size_human' => $this->formatBytes($size),
            'driver' => $driver,
            'database' => $databaseName,
            'compressed' => $useCompression,
            'method' => $method,
            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Liste toutes les sauvegardes SQL présentes sur le disque privé, de la plus récente à la plus ancienne.
     *
     * @return array<int, array{
     *     filename: string,
     *     size: int,
     *     size_human: string,
     *     created_at: string,
     *     timestamp: int,
     *     compressed: bool
     * }>
     */
    public function listBackups(): array
    {
        $disk = Storage::disk('local');

        if (! $disk->exists(self::BACKUP_DIRECTORY)) {
            return [];
        }

        $backups = [];

        foreach ($disk->files(self::BACKUP_DIRECTORY) as $relativePath) {
            $filename = basename($relativePath);

            if (! $this->isValidBackupFilename($filename)) {
                continue;
            }

            $size = $disk->size($relativePath);
            $timestamp = $disk->lastModified($relativePath);

            $backups[] = [
                'filename' => $filename,
                'size' => $size,
                'size_human' => $this->formatBytes($size),
                'created_at' => date('Y-m-d H:i:s', $timestamp),
                'timestamp' => $timestamp,
                'compressed' => str_ends_with($filename, '.gz'),
            ];
        }

        usort($backups, static fn (array $first, array $second): int => $second['timestamp'] <=> $first['timestamp']);

        return $backups;
    }

    /**
     * Valide un nom de fichier de sauvegarde et retourne son chemin absolu.
     */
    public function resolveBackupPath(string $filename): string
    {
        if (! $this->isValidBackupFilename($filename)) {
            throw new InvalidArgumentException("BACKUP_INVALID_FILENAME: Nom de fichier de sauvegarde invalide ({$filename}).");
        }

        $relativePath = self::BACKUP_DIRECTORY.'/'.$filename;
        $disk = Storage::disk('local');

        if (! $disk->exists($relativePath)) {
            throw new RuntimeException("BACKUP_NOT_FOUND: La sauvegarde « {$filename} » est introuvable.");
        }

        return $disk->path($relativePath);
    }

    /**
     * Supprime une sauvegarde existante.
     */
    public function deleteBackup(string $filename): void
    {
        $this->resolveBackupPath($filename);
        Storage::disk('local')->delete(self::BACKUP_DIRECTORY.'/'.$filename);
    }

    /**
     * Conserve les $keep sauvegardes les plus récentes et supprime les plus anciennes.
     */
    public function pruneOldBackups(int $keep = 10): int
    {
        if ($keep < 0) {
            throw new InvalidArgumentException('BACKUP_INVALID_RETENTION: Le nombre de sauvegardes à conserver doit être positif ou nul.');
        }

        $backups = $this->listBackups();
        $toDelete = array_slice($backups, $keep);
        $deletedCount = 0;

        foreach ($toDelete as $backup) {
            Storage::disk('local')->delete(self::BACKUP_DIRECTORY.'/'.$backup['filename']);
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Vérifie que le nom de fichier respecte strictement le format attendu (anti path-traversal).
     */
    public function isValidBackupFilename(string $filename): bool
    {
        if ($filename === '' || str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            return false;
        }

        return (bool) preg_match('/^[A-Za-z0-9._-]+\.sql(\.gz)?$/', $filename);
    }

    /**
     * Formate une taille en octets en chaîne lisible.
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' o';
        }

        $units = ['Ko', 'Mo', 'Go', 'To'];
        $size = (float) $bytes;

        foreach ($units as $unit) {
            $size /= 1024;

            if ($size < 1024 || $unit === 'To') {
                return number_format($size, 2, ',', ' ').' '.$unit;
            }
        }

        return $bytes.' o';
    }

    /**
     * Nettoie le nom de la base pour la construction du nom de fichier.
     */
    public function sanitizeDatabaseName(string $database): string
    {
        $base = $database === ':memory:' ? 'memory' : pathinfo($database, PATHINFO_FILENAME);
        $slug = Str::slug($base ?: 'database', '_');

        return $slug !== '' ? $slug : 'database';
    }

    /**
     * Exporte une base MySQL/MariaDB via mysqldump avec repli automatique en PDO pur PHP.
     *
     * @param  array<string, mixed>  $connectionConfig
     */
    private function dumpMySql(string $connectionName, array $connectionConfig, string $outputPath): string
    {
        if ($this->tryNativeMySqlDump($connectionConfig, $outputPath)) {
            return 'mysqldump';
        }

        return $this->dumpMySqlViaPdo($connectionName, $outputPath);
    }

    /**
     * Tente un dump natif via mysqldump ou mariadb-dump.
     *
     * @param  array<string, mixed>  $connectionConfig
     */
    private function tryNativeMySqlDump(array $connectionConfig, string $outputPath): bool
    {
        $binary = $this->findExecutable(['mysqldump', 'mariadb-dump']);

        if ($binary === null) {
            return false;
        }

        $command = [
            $binary,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--result-file='.$outputPath,
        ];

        $socket = (string) ($connectionConfig['unix_socket'] ?? '');
        if ($socket !== '') {
            $command[] = '--socket='.$socket;
        } else {
            $command[] = '--host='.(string) ($connectionConfig['host'] ?? '127.0.0.1');
            $command[] = '--port='.(string) ($connectionConfig['port'] ?? '3306');
        }

        $command[] = '--user='.(string) ($connectionConfig['username'] ?? 'root');
        $command[] = (string) ($connectionConfig['database'] ?? '');

        $env = [];
        $password = (string) ($connectionConfig['password'] ?? '');
        if ($password !== '') {
            $env['MYSQL_PWD'] = $password;
        }

        $processResult = Process::timeout(600)->env($env)->run($command);

        if (! $processResult->successful() || ! is_file($outputPath) || filesize($outputPath) === 0) {
            @unlink($outputPath);

            return false;
        }

        return true;
    }

    /**
     * Exporte une base MySQL/MariaDB en PHP pur via PDO (fonctionne même sans binaire mysqldump).
     */
    private function dumpMySqlViaPdo(string $connectionName, string $outputPath): string
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $handle = $this->openWriteHandle($outputPath);

        try {
            fwrite($handle, $this->buildSqlHeader($connectionName, 'mysql'));
            fwrite($handle, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            $tablesStatement = $pdo->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
            $tables = $tablesStatement !== false ? $tablesStatement->fetchAll(PDO::FETCH_NUM) : [];

            foreach ($tables as $tableRow) {
                $tableName = (string) $tableRow[0];
                $quotedTable = $this->quoteIdentifier($tableName, 'mysql');

                $createStatement = $pdo->query("SHOW CREATE TABLE {$quotedTable}");
                $createRow = $createStatement !== false ? $createStatement->fetch(PDO::FETCH_NUM) : false;

                if (is_array($createRow) && isset($createRow[1])) {
                    fwrite($handle, "-- Structure de la table {$quotedTable}\n");
                    fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
                    fwrite($handle, $createRow[1].";\n\n");
                }

                $this->writeTableInserts($pdo, $handle, $tableName, 'mysql');
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            fclose($handle);
        }

        return 'pdo-php';
    }

    /**
     * Exporte une base PostgreSQL via pg_dump avec repli PDO.
     *
     * @param  array<string, mixed>  $connectionConfig
     */
    private function dumpPostgres(string $connectionName, array $connectionConfig, string $outputPath): string
    {
        if ($this->tryNativePgDump($connectionConfig, $outputPath)) {
            return 'pg_dump';
        }

        return $this->dumpPostgresViaPdo($connectionName, $outputPath);
    }

    /**
     * Tente un dump natif via pg_dump.
     *
     * @param  array<string, mixed>  $connectionConfig
     */
    private function tryNativePgDump(array $connectionConfig, string $outputPath): bool
    {
        $binary = $this->findExecutable(['pg_dump']);

        if ($binary === null) {
            return false;
        }

        $command = [
            $binary,
            '--no-owner',
            '--no-acl',
            '--host='.(string) ($connectionConfig['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connectionConfig['port'] ?? '5432'),
            '--username='.(string) ($connectionConfig['username'] ?? 'postgres'),
            '--file='.$outputPath,
            (string) ($connectionConfig['database'] ?? ''),
        ];

        $env = [];
        $password = (string) ($connectionConfig['password'] ?? '');
        if ($password !== '') {
            $env['PGPASSWORD'] = $password;
        }

        $processResult = Process::timeout(600)->env($env)->run($command);

        if (! $processResult->successful() || ! is_file($outputPath) || filesize($outputPath) === 0) {
            @unlink($outputPath);

            return false;
        }

        return true;
    }

    /**
     * Repli PDO pour PostgreSQL (données des tables du schéma public).
     */
    private function dumpPostgresViaPdo(string $connectionName, string $outputPath): string
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $handle = $this->openWriteHandle($outputPath);

        try {
            fwrite($handle, $this->buildSqlHeader($connectionName, 'pgsql'));
            fwrite($handle, "BEGIN;\n\n");

            $tablesStatement = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
            $tables = $tablesStatement !== false ? $tablesStatement->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach ($tables as $tableName) {
                $this->writeTableInserts($pdo, $handle, (string) $tableName, 'pgsql');
            }

            fwrite($handle, "COMMIT;\n");
        } finally {
            fclose($handle);
        }

        return 'pdo-php';
    }

    /**
     * Exporte une base SQLite (fichier ou :memory:) en SQL portable via PDO.
     */
    private function dumpSqliteViaPdo(string $connectionName, string $outputPath): string
    {
        $pdo = DB::connection($connectionName)->getPdo();
        $handle = $this->openWriteHandle($outputPath);

        try {
            fwrite($handle, $this->buildSqlHeader($connectionName, 'sqlite'));
            fwrite($handle, "PRAGMA foreign_keys=OFF;\nBEGIN TRANSACTION;\n\n");

            $tablesStatement = $pdo->query(
                "SELECT name, sql FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            );
            $tables = $tablesStatement !== false ? $tablesStatement->fetchAll(PDO::FETCH_ASSOC) : [];

            foreach ($tables as $table) {
                $tableName = (string) $table['name'];
                $createSql = (string) ($table['sql'] ?? '');
                $quotedTable = $this->quoteIdentifier($tableName, 'sqlite');

                if ($createSql !== '') {
                    fwrite($handle, "-- Structure de la table {$quotedTable}\n");
                    fwrite($handle, "DROP TABLE IF EXISTS {$quotedTable};\n");
                    fwrite($handle, rtrim($createSql, ';').";\n\n");
                }

                $this->writeTableInserts($pdo, $handle, $tableName, 'sqlite');
            }

            $extrasStatement = $pdo->query(
                "SELECT sql FROM sqlite_master WHERE type IN ('index', 'trigger', 'view') AND sql IS NOT NULL AND name NOT LIKE 'sqlite_%' ORDER BY type, name"
            );
            $extras = $extrasStatement !== false ? $extrasStatement->fetchAll(PDO::FETCH_COLUMN) : [];

            foreach ($extras as $extraSql) {
                fwrite($handle, rtrim((string) $extraSql, ';').";\n");
            }

            fwrite($handle, "\nCOMMIT;\nPRAGMA foreign_keys=ON;\n");
        } finally {
            fclose($handle);
        }

        return 'pdo-php';
    }

    /**
     * Écrit les instructions INSERT par lots pour une table donnée.
     *
     * @param  resource  $handle
     */
    private function writeTableInserts(PDO $pdo, $handle, string $tableName, string $driver): void
    {
        $quotedTable = $this->quoteIdentifier($tableName, $driver);
        $statement = $pdo->query("SELECT * FROM {$quotedTable}");

        if ($statement === false) {
            return;
        }

        $columnList = null;
        $batch = [];

        while (($row = $statement->fetch(PDO::FETCH_ASSOC)) !== false) {
            if ($columnList === null) {
                $quotedColumns = array_map(
                    fn (string $column): string => $this->quoteIdentifier($column, $driver),
                    array_keys($row)
                );
                $columnList = implode(', ', $quotedColumns);
            }

            $values = array_map(
                fn (mixed $value): string => $this->formatSqlValue($pdo, $value),
                array_values($row)
            );
            $batch[] = '('.implode(', ', $values).')';

            if (count($batch) >= self::INSERT_BATCH_SIZE) {
                fwrite($handle, "INSERT INTO {$quotedTable} ({$columnList}) VALUES\n".implode(",\n", $batch).";\n");
                $batch = [];
            }
        }

        if ($batch !== [] && $columnList !== null) {
            fwrite($handle, "INSERT INTO {$quotedTable} ({$columnList}) VALUES\n".implode(",\n", $batch).";\n\n");
        }
    }

    /**
     * Compresse un fichier SQL au format gzip par flux (faible empreinte mémoire).
     */
    private function compressFile(string $sourcePath, string $destinationPath): void
    {
        $source = fopen($sourcePath, 'rb');
        if ($source === false) {
            throw new RuntimeException("BACKUP_READ_FAILED: Impossible d'ouvrir le fichier temporaire pour compression.");
        }

        $target = gzopen($destinationPath, 'wb9');
        if ($target === false) {
            fclose($source);

            throw new RuntimeException("BACKUP_COMPRESS_FAILED: Impossible de créer l'archive gzip.");
        }

        try {
            while (! feof($source)) {
                $chunk = fread($source, 1024 * 512);
                if ($chunk !== false && $chunk !== '') {
                    gzwrite($target, $chunk);
                }
            }
        } finally {
            fclose($source);
            gzclose($target);
        }
    }

    /**
     * Recherche le premier binaire exécutable disponible sur le système.
     *
     * @param  array<int, string>  $candidates
     */
    private function findExecutable(array $candidates): ?string
    {
        $lookupCommand = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';

        foreach ($candidates as $candidate) {
            $lookup = Process::timeout(5)->run([$lookupCommand, $candidate]);

            if ($lookup->successful()) {
                $firstLine = trim(strtok($lookup->output(), "\r\n") ?: '');

                if ($firstLine !== '') {
                    return $firstLine;
                }
            }
        }

        return null;
    }

    /**
     * Ouvre un fichier en écriture binaire.
     *
     * @return resource
     */
    private function openWriteHandle(string $path)
    {
        $handle = fopen($path, 'wb');

        if ($handle === false) {
            throw new RuntimeException("BACKUP_WRITE_FAILED: Impossible de créer le fichier de sauvegarde « {$path} ».");
        }

        return $handle;
    }

    private function buildSqlHeader(string $connectionName, string $driver): string
    {
        $generatedAt = now()->toIso8601String();

        return "-- Hubs Africa Database Dump\n"
            ."-- Connexion : {$connectionName} ({$driver})\n"
            ."-- Généré le : {$generatedAt}\n\n";
    }

    private function quoteIdentifier(string $identifier, string $driver): string
    {
        if ($driver === 'mysql' || $driver === 'mariadb') {
            return '`'.str_replace('`', '``', $identifier).'`';
        }

        return '"'.str_replace('"', '""', $identifier).'"';
    }

    private function formatSqlValue(PDO $pdo, mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $pdo->quote((string) $value);
    }
}
