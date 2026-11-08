<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\Sae;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class OpportunityImportService
{
    /**
     * Importe des opportunités depuis un fichier JSON, CSV ou Excel (XLSX).
     *
     * @param  string  $filePath  Chemin absolu vers le fichier
     * @param  int|Sae|null  $sae  La structure SAE rattachée
     * @return array{imported_count: int, skipped_count: int, errors: array<int, string>}
     */
    public function importFromFile(string $filePath, int|Sae|null $sae): array
    {
        $this->ensureOpportunitiesTableExists();

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            throw new InvalidArgumentException("Le fichier d'import est introuvable ou illisible : {$filePath}");
        }

        $saeId = $this->resolveSaeId($sae);
        $extension = Str::lower(pathinfo($filePath, PATHINFO_EXTENSION));

        $rawRows = match ($extension) {
            'json' => $this->parseJsonFile($filePath),
            'csv' => $this->parseCsvFile($filePath),
            'xlsx' => $this->parseXlsxFile($filePath),
            default => throw new InvalidArgumentException("Format de fichier non pris en charge (.{$extension}). Formats acceptés : .json, .csv, .xlsx"),
        };

        return $this->importRows($rawRows, $saeId);
    }

    /**
     * Importe un ensemble de lignes normalisées associées à la structure SAE.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{imported_count: int, skipped_count: int, errors: array<int, string>}
     */
    public function importRows(array $rows, int $saeId): array
    {
        $this->ensureOpportunitiesTableExists();

        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 1;

            $title = trim((string) ($row['title'] ?? $row['titre'] ?? $row['nom'] ?? ''));
            if ($title === '') {
                $skippedCount++;
                $errors[] = "Ligne {$lineNumber} : Le titre de l'opportunité est manquant ou vide.";

                continue;
            }

            try {
                $sectors = $this->normalizeArrayField($row['sectors'] ?? $row['secteurs'] ?? []);
                $targetZones = $this->normalizeArrayField($row['target_zones'] ?? $row['zones'] ?? $row['zones_concernees'] ?? []);
                $details = (string) ($row['details'] ?? $row['description'] ?? '');
                $learnMoreUrl = $this->normalizeUrl($row['learn_more_url'] ?? $row['en_savoir_plus'] ?? $row['lien_info'] ?? null);
                $applyUrl = $this->normalizeUrl($row['apply_url'] ?? $row['postuler'] ?? $row['lien_postuler'] ?? null);
                $deadline = $this->parseDeadlineDate($row['deadline'] ?? $row['date_limite'] ?? null);
                $image = ! empty($row['image']) ? trim((string) $row['image']) : null;
                $isActive = isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true : true;

                // Si aucune image n'est fournie, tentative d'extraction automatique depuis le lien public
                if ($image === null && ($learnMoreUrl || $applyUrl)) {
                    $targetUrl = $learnMoreUrl ?: $applyUrl;
                    try {
                        $scraper = app(WebpageMetadataScraperService::class);
                        $metadata = $scraper->scrapeFromUrl($targetUrl);
                        if ($metadata->hasImage()) {
                            $image = $scraper->downloadAndStoreCoverImage($metadata->imageUrl);
                        }
                    } catch (\Throwable $scrapeException) {
                        Log::info("Extraction automatique du visuel ignorée pour {$targetUrl} : ".$scrapeException->getMessage());
                    }
                }

                $baseSlug = Str::slug($title);
                $uniqueSuffix = Str::lower(Str::random(6));
                $slug = "{$baseSlug}-{$uniqueSuffix}";

                Opportunity::create([
                    'sae_id' => $saeId,
                    'title' => $title,
                    'slug' => $slug,
                    'image' => $image,
                    'sectors' => $sectors,
                    'target_zones' => $targetZones,
                    'details' => $details !== '' ? $details : "<p>{$title}</p>",
                    'learn_more_url' => $learnMoreUrl,
                    'apply_url' => $applyUrl,
                    'deadline' => $deadline,
                    'is_active' => $isActive,
                ]);

                $importedCount++;
            } catch (\Throwable $exception) {
                Log::error("Erreur lors de l'import de l'opportunité '{$title}' : ".$exception->getMessage());
                $skippedCount++;
                $errors[] = "Ligne {$lineNumber} ('{$title}') : ".$exception->getMessage();
            }
        }

        return [
            'imported_count' => $importedCount,
            'skipped_count' => $skippedCount,
            'errors' => $errors,
        ];
    }

    /**
     * S'assure que la table 'opportunities' existe dans la base de données.
     * En production, si la table est absente, déclenche automatiquement la migration.
     */
    private function ensureOpportunitiesTableExists(): void
    {
        if (Schema::hasTable('opportunities')) {
            return;
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $exception) {
            Log::warning('Tentative automatique de migration échouée : '.$exception->getMessage());
        }

        if (! Schema::hasTable('opportunities')) {
            throw new RuntimeException(
                "La table 'opportunities' n'existe pas encore dans la base de données de production. "
                .'Veuillez exécuter les migrations en accédant à : /maintenance/migrate-force?key=hubsafrica-migrate-force-2026'
            );
        }
    }

    /**
     * Résout l'identifiant SAE de façon fiable avec fallbacks contextuels.
     */
    private function resolveSaeId(int|Sae|null $sae): int
    {
        if ($sae instanceof Sae) {
            return $sae->id;
        }

        if (is_numeric($sae) && (int) $sae > 0) {
            return (int) $sae;
        }

        if (function_exists('filament') && filament()->hasTenancy() && ($tenant = filament()->getTenant())) {
            return (int) $tenant->getKey();
        }

        if (auth()->check() && isset(auth()->user()->active_sae_id)) {
            return (int) auth()->user()->active_sae_id;
        }

        $cubeSae = Sae::where('name', 'like', '%CUBE%')
            ->orWhere('slug', 'like', '%cube%')
            ->first() ?: Sae::first();

        if ($cubeSae) {
            return (int) $cubeSae->id;
        }

        throw new RuntimeException("Aucune structure d'accompagnement (SAE) valide n'a pu être trouvée pour associer les opportunités.");
    }

    /**
     * Parse un fichier JSON en supprimant l'éventuel BOM UTF-8.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parseJsonFile(string $filePath): array
    {
        $rawContent = file_get_contents($filePath);
        if ($rawContent === false) {
            throw new InvalidArgumentException("Impossible de lire le fichier JSON : {$filePath}");
        }

        $cleanedContent = preg_replace('/^\xEF\xBB\xBF/', '', trim($rawContent));
        $decoded = json_decode((string) $cleanedContent, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            throw new InvalidArgumentException('Structure JSON invalide : '.json_last_error_msg());
        }

        if (isset($decoded['opportunities']) && is_array($decoded['opportunities'])) {
            return $decoded['opportunities'];
        }

        if (isset($decoded['data']) && is_array($decoded['data'])) {
            return $decoded['data'];
        }

        return $decoded;
    }

    /**
     * Parse un fichier CSV avec détection automatique du délimiteur.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parseCsvFile(string $filePath): array
    {
        $fileHandle = fopen($filePath, 'r');
        if (! $fileHandle) {
            throw new InvalidArgumentException("Impossible d'ouvrir le fichier CSV : {$filePath}");
        }

        $firstLine = fgets($fileHandle);
        rewind($fileHandle);

        $delimiter = ',';
        if ($firstLine !== false) {
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $headers = fgetcsv($fileHandle, 0, $delimiter);
        if (! $headers) {
            fclose($fileHandle);

            return [];
        }

        $cleanHeaders = array_map(function ($header) {
            $cleaned = trim((string) $header);
            $cleaned = preg_replace('/[\x{FEFF}]/u', '', $cleaned);

            return Str::slug(Str::lower($cleaned), '_');
        }, $headers);

        $rows = [];
        while (($data = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
            if (count($data) === 1 && empty($data[0])) {
                continue;
            }

            $row = [];
            foreach ($cleanHeaders as $colIndex => $key) {
                if ($key !== '' && isset($data[$colIndex])) {
                    $row[$key] = $data[$colIndex];
                }
            }

            if (! empty($row)) {
                $rows[] = $row;
            }
        }

        fclose($fileHandle);

        return $rows;
    }

    /**
     * Parse un fichier Excel (.xlsx) natif via ZipArchive & XML.
     *
     * @return array<int, array<string, mixed>>
     */
    private function parseXlsxFile(string $filePath): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            throw new InvalidArgumentException("Impossible d'ouvrir le classeur Excel : {$filePath}");
        }

        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml) {
                foreach ($xml->si as $si) {
                    $text = '';
                    if (isset($si->t)) {
                        $text = (string) $si->t;
                    } elseif (isset($si->r)) {
                        foreach ($si->r as $run) {
                            $text .= (string) $run->t;
                        }
                    }
                    $sharedStrings[] = $text;
                }
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new InvalidArgumentException("Feuille principale 'sheet1.xml' introuvable dans : {$filePath}");
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $matrix = [];
        foreach ($sheet->sheetData->row as $rowNode) {
            $rowIndex = (int) $rowNode['r'];
            $rowCells = [];

            foreach ($rowNode->c as $cell) {
                $cellRef = (string) $cell['r'];
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $type = (string) $cell['t'];
                $val = isset($cell->v) ? (string) $cell->v : '';

                if ($type === 's' && isset($sharedStrings[(int) $val])) {
                    $cellValue = $sharedStrings[(int) $val];
                } else {
                    $cellValue = $val;
                }

                $rowCells[$colLetters] = trim($cellValue);
            }

            $matrix[$rowIndex] = $rowCells;
        }

        if (empty($matrix)) {
            return [];
        }

        ksort($matrix);
        $headerRow = array_shift($matrix);
        if (! $headerRow) {
            return [];
        }

        $headerMap = [];
        foreach ($headerRow as $colLetter => $name) {
            $headerMap[$colLetter] = Str::slug(Str::lower($name), '_');
        }

        $resultRows = [];
        foreach ($matrix as $dataRow) {
            $item = [];
            foreach ($headerMap as $colLetter => $key) {
                if ($key !== '' && isset($dataRow[$colLetter])) {
                    $item[$key] = $dataRow[$colLetter];
                }
            }

            if (! empty($item)) {
                $resultRows[] = $item;
            }
        }

        return $resultRows;
    }

    /**
     * Normalise un champ susceptible d'être une chaîne délimitée ou un tableau natif.
     *
     * @return array<int, string>
     */
    private function normalizeArrayField(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($item) => $item !== ''));
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $parts = preg_split('/[,;|]/', $value);
        if (! is_array($parts)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $parts), fn ($item) => $item !== ''));
    }

    /**
     * Valide et normalise une URL ou un lien mailto.
     */
    private function normalizeUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $trimmed = trim($url);
        if (str_starts_with($trimmed, 'mailto:')) {
            return $trimmed;
        }

        if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
            return $trimmed;
        }

        return null;
    }

    /**
     * Tente de parser une date limite sous divers formats usuels.
     */
    private function parseDeadlineDate(mixed $rawDate): ?Carbon
    {
        if (blank($rawDate)) {
            return null;
        }

        if ($rawDate instanceof Carbon) {
            return $rawDate;
        }

        try {
            return Carbon::parse($rawDate);
        } catch (\Throwable) {
            return null;
        }
    }
}
