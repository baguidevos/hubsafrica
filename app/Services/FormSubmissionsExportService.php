<?php

namespace App\Services;

use App\Models\Cohort;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Packstub\FormBuilder\Filament\SubmissionsExport;
use Packstub\FormBuilder\Models\Form;
use Packstub\FormBuilder\Models\FormSubmission;

class FormSubmissionsExportService extends SubmissionsExport
{
    /** @var list<string> */
    private const PROGRAM_ID_KEYS = ['program_id', 'programme_id'];

    /** @var list<string> */
    private const COHORT_ID_KEYS = ['cohort_id', 'cohorte_id'];

    /**
     * Remplace les colonnes/valeurs `program_id` et `cohort_id` par les noms lisibles
     * du Programme et de la Cohorte dans les exports CSV et Excel.
     *
     * @param  Builder<FormSubmission>|Collection<int, FormSubmission>  $submissions
     * @return array{0: array<int, string>, 1: iterable<int, array<int, mixed>>}
     */
    public static function rows(Form $form, Builder|Collection $submissions): array
    {
        $records = $submissions instanceof Builder ? $submissions->orderBy('created_at')->get() : $submissions;
        $keys = $form->allInputFields()->keys()->all();

        foreach ($records as $record) {
            foreach (array_keys($record->data ?? []) as $key) {
                if (! in_array($key, $keys, true)) {
                    $keys[] = $key;
                }
            }
        }

        $programNames = static::resolveProgramNames($records);
        $cohortNames = static::resolveCohortNames($records);

        $labels = array_map(
            fn (string $key): string => static::resolveHeaderLabel($form, $key),
            $keys,
        );

        $header = ['number', 'id', 'submitted_at', ...$labels, 'source_url', 'ip', 'user_id'];

        $rows = (function () use ($records, $keys, $programNames, $cohortNames): \Generator {
            foreach ($records as $record) {
                $formatted = $record->formatted();
                $rawData = (array) ($record->data ?? []);

                yield [
                    $record->number,
                    $record->getKey(),
                    $record->created_at?->toDateTimeString(),
                    ...array_map(
                        fn (string $key): string => static::resolveCellValue(
                            key: $key,
                            formattedValue: (string) ($formatted[$key]['value'] ?? ''),
                            rawValue: $rawData[$key] ?? null,
                            programNames: $programNames,
                            cohortNames: $cohortNames,
                        ),
                        $keys,
                    ),
                    $record->source_url,
                    $record->ip,
                    $record->user_id,
                ];
            }
        })();

        return [$header, $rows];
    }

    /**
     * @param  Builder<FormSubmission>|Collection<int, FormSubmission>  $submissions
     */
    public static function write(Form $form, Builder|Collection $submissions, mixed $handle = null): void
    {
        $handle ??= fopen('php://output', 'w');

        if ($handle === false) {
            return;
        }

        fwrite($handle, "\xEF\xBB\xBF");

        [$header, $rows] = static::rows($form, $submissions);

        fputcsv($handle, $header);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        if (is_resource($handle) && stream_get_meta_data($handle)['uri'] === 'php://output') {
            fclose($handle);
        }
    }

    private static function resolveHeaderLabel(Form $form, string $key): string
    {
        $normalizedKey = strtolower($key);

        if (in_array($normalizedKey, self::PROGRAM_ID_KEYS, true)) {
            return 'Programme';
        }

        if (in_array($normalizedKey, self::COHORT_ID_KEYS, true)) {
            return 'Cohorte';
        }

        return $form->allInputFields()->get($key)?->label ?? $key;
    }

    /**
     * @param  array<int, string>  $programNames
     * @param  array<int, string>  $cohortNames
     */
    private static function resolveCellValue(
        string $key,
        string $formattedValue,
        mixed $rawValue,
        array $programNames,
        array $cohortNames,
    ): string {
        $normalizedKey = strtolower($key);
        $candidate = is_scalar($rawValue) && (string) $rawValue !== '' ? (string) $rawValue : $formattedValue;

        if (in_array($normalizedKey, self::PROGRAM_ID_KEYS, true) && ctype_digit($candidate)) {
            return $programNames[(int) $candidate] ?? $formattedValue;
        }

        if (in_array($normalizedKey, self::COHORT_ID_KEYS, true) && ctype_digit($candidate)) {
            return $cohortNames[(int) $candidate] ?? $formattedValue;
        }

        return $formattedValue;
    }

    /**
     * @param  Collection<int, FormSubmission>  $records
     * @return array<int, string>
     */
    private static function resolveProgramNames(Collection $records): array
    {
        $ids = static::extractNumericIds($records, self::PROGRAM_ID_KEYS);

        if ($ids === []) {
            return [];
        }

        return Program::withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->pluck('name', 'id')
            ->mapWithKeys(fn (string $name, int|string $id): array => [(int) $id => $name])
            ->all();
    }

    /**
     * @param  Collection<int, FormSubmission>  $records
     * @return array<int, string>
     */
    private static function resolveCohortNames(Collection $records): array
    {
        $ids = static::extractNumericIds($records, self::COHORT_ID_KEYS);

        if ($ids === []) {
            return [];
        }

        return Cohort::withoutGlobalScopes()
            ->whereIn('id', $ids)
            ->pluck('name', 'id')
            ->mapWithKeys(fn (string $name, int|string $id): array => [(int) $id => $name])
            ->all();
    }

    /**
     * @param  Collection<int, FormSubmission>  $records
     * @param  list<string>  $targetKeys
     * @return list<int>
     */
    private static function extractNumericIds(Collection $records, array $targetKeys): array
    {
        $ids = [];

        foreach ($records as $record) {
            foreach ((array) ($record->data ?? []) as $key => $value) {
                if (! in_array(strtolower((string) $key), $targetKeys, true)) {
                    continue;
                }

                if (is_scalar($value) && ctype_digit((string) $value)) {
                    $ids[(int) $value] = true;
                }
            }
        }

        return array_keys($ids);
    }
}
