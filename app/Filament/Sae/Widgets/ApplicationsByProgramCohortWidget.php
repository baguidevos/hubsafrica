<?php

namespace App\Filament\Sae\Widgets;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Program;
use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class ApplicationsByProgramCohortWidget extends Widget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.sae.widgets.applications-by-program-cohort-widget';

    public string $selectedProgramId = '';

    /**
     * @return array{
     *     total_applications: int,
     *     programs_count: int,
     *     cohorts_count: int,
     *     program_options: array<int, string>,
     *     programs: list<array{
     *         program_id: int|null,
     *         program_name: string,
     *         total: int,
     *         share: float,
     *         accepted: int,
     *         pending: int,
     *         waitlisted: int,
     *         rejected: int,
     *         cohorts: list<array{
     *             cohort_id: int|null,
     *             cohort_name: string,
     *             total: int,
     *             program_share: float,
     *             global_share: float,
     *             accepted: int,
     *             pending: int,
     *             waitlisted: int,
     *             rejected: int,
     *         }>,
     *     }>,
     * }
     */
    public function getDistributionData(): array
    {
        $groupedRows = Application::query()
            ->where('status', '!=', ApplicationStatus::BROUILLON->value)
            ->selectRaw('program_id, cohort_id, status, COUNT(*) as aggregate_count')
            ->groupBy('program_id', 'cohort_id', 'status')
            ->get()
            ->map(static fn (Application $row): array => [
                'program_id' => $row->program_id !== null ? (int) $row->program_id : null,
                'cohort_id' => $row->cohort_id !== null ? (int) $row->cohort_id : null,
                'status' => $row->status instanceof ApplicationStatus ? $row->status->value : (string) $row->status,
                'count' => (int) $row->getAttribute('aggregate_count'),
            ])
            ->all();

        if ($groupedRows === []) {
            return self::emptyDistribution();
        }

        $programIds = array_values(array_unique(array_filter(
            array_column($groupedRows, 'program_id'),
            static fn (?int $id): bool => $id !== null,
        )));

        $cohortIds = array_values(array_unique(array_filter(
            array_column($groupedRows, 'cohort_id'),
            static fn (?int $id): bool => $id !== null,
        )));

        $programNames = $programIds !== []
            ? Program::query()->whereIn('id', $programIds)->pluck('name', 'id')->all()
            : [];

        $cohortNames = $cohortIds !== []
            ? Cohort::query()->whereIn('id', $cohortIds)->pluck('name', 'id')->all()
            : [];

        $selectedProgramFilter = ctype_digit($this->selectedProgramId)
            ? (int) $this->selectedProgramId
            : null;

        return self::buildDistribution(
            $groupedRows,
            $programNames,
            $cohortNames,
            $selectedProgramFilter,
        );
    }

    /**
     * Agrège les candidatures par programme puis par cohorte (fonction pure).
     *
     * @param  list<array{program_id: int|null, cohort_id: int|null, status: string, count: int}>  $groupedRows
     * @param  array<int, string>  $programNames
     * @param  array<int, string>  $cohortNames
     * @return array{
     *     total_applications: int,
     *     programs_count: int,
     *     cohorts_count: int,
     *     program_options: array<int, string>,
     *     programs: list<array{
     *         program_id: int|null,
     *         program_name: string,
     *         total: int,
     *         share: float,
     *         accepted: int,
     *         pending: int,
     *         waitlisted: int,
     *         rejected: int,
     *         cohorts: list<array{
     *             cohort_id: int|null,
     *             cohort_name: string,
     *             total: int,
     *             program_share: float,
     *             global_share: float,
     *             accepted: int,
     *             pending: int,
     *             waitlisted: int,
     *             rejected: int,
     *         }>,
     *     }>,
     * }
     */
    public static function buildDistribution(
        array $groupedRows,
        array $programNames,
        array $cohortNames,
        ?int $selectedProgramId = null,
    ): array {
        if ($groupedRows === []) {
            return self::emptyDistribution();
        }

        $totalApplications = 0;
        $programBuckets = [];
        $distinctCohortIds = [];

        foreach ($groupedRows as $row) {
            $count = max(0, (int) $row['count']);
            if ($count === 0) {
                continue;
            }

            $programId = $row['program_id'];
            $cohortId = $row['cohort_id'];
            $status = $row['status'];
            $programKey = $programId !== null ? (string) $programId : 'none';
            $cohortKey = $cohortId !== null ? (string) $cohortId : 'none';

            $totalApplications += $count;

            if ($cohortId !== null) {
                $distinctCohortIds[$cohortId] = true;
            }

            if (! isset($programBuckets[$programKey])) {
                $programBuckets[$programKey] = [
                    'program_id' => $programId,
                    'program_name' => $programId !== null
                        ? ($programNames[$programId] ?? 'Programme supprimé')
                        : 'Sans programme',
                    'total' => 0,
                    'accepted' => 0,
                    'pending' => 0,
                    'waitlisted' => 0,
                    'rejected' => 0,
                    'cohorts' => [],
                ];
            }

            if (! isset($programBuckets[$programKey]['cohorts'][$cohortKey])) {
                $programBuckets[$programKey]['cohorts'][$cohortKey] = [
                    'cohort_id' => $cohortId,
                    'cohort_name' => $cohortId !== null
                        ? ($cohortNames[$cohortId] ?? 'Cohorte supprimée')
                        : 'Sans cohorte assignée',
                    'total' => 0,
                    'accepted' => 0,
                    'pending' => 0,
                    'waitlisted' => 0,
                    'rejected' => 0,
                ];
            }

            self::incrementStatusCounters($programBuckets[$programKey], $status, $count);
            self::incrementStatusCounters($programBuckets[$programKey]['cohorts'][$cohortKey], $status, $count);
        }

        if ($totalApplications === 0) {
            return self::emptyDistribution();
        }

        $programs = [];
        foreach ($programBuckets as $programBucket) {
            $cohorts = [];
            foreach ($programBucket['cohorts'] as $cohortBucket) {
                $cohorts[] = [
                    'cohort_id' => $cohortBucket['cohort_id'],
                    'cohort_name' => $cohortBucket['cohort_name'],
                    'total' => $cohortBucket['total'],
                    'program_share' => round(($cohortBucket['total'] / $programBucket['total']) * 100, 1),
                    'global_share' => round(($cohortBucket['total'] / $totalApplications) * 100, 1),
                    'accepted' => $cohortBucket['accepted'],
                    'pending' => $cohortBucket['pending'],
                    'waitlisted' => $cohortBucket['waitlisted'],
                    'rejected' => $cohortBucket['rejected'],
                ];
            }

            usort($cohorts, static function (array $left, array $right): int {
                if ($right['total'] !== $left['total']) {
                    return $right['total'] <=> $left['total'];
                }

                return strcasecmp($left['cohort_name'], $right['cohort_name']);
            });

            $programs[] = [
                'program_id' => $programBucket['program_id'],
                'program_name' => $programBucket['program_name'],
                'total' => $programBucket['total'],
                'share' => round(($programBucket['total'] / $totalApplications) * 100, 1),
                'accepted' => $programBucket['accepted'],
                'pending' => $programBucket['pending'],
                'waitlisted' => $programBucket['waitlisted'],
                'rejected' => $programBucket['rejected'],
                'cohorts' => $cohorts,
            ];
        }

        usort($programs, static function (array $left, array $right): int {
            if ($right['total'] !== $left['total']) {
                return $right['total'] <=> $left['total'];
            }

            return strcasecmp($left['program_name'], $right['program_name']);
        });

        $programOptions = [];
        foreach ($programs as $program) {
            if ($program['program_id'] !== null) {
                $programOptions[$program['program_id']] = $program['program_name'];
            }
        }

        if ($selectedProgramId !== null) {
            $programs = array_values(array_filter(
                $programs,
                static fn (array $program): bool => $program['program_id'] === $selectedProgramId,
            ));
        }

        return [
            'total_applications' => $totalApplications,
            'programs_count' => count($programBuckets),
            'cohorts_count' => count($distinctCohortIds),
            'program_options' => $programOptions,
            'programs' => $programs,
        ];
    }

    public function getApplicationsUrl(?int $programId = null, ?int $cohortId = null): ?string
    {
        $tenant = Filament::getTenant();
        if (! $tenant) {
            return null;
        }

        $filters = [];

        if ($programId !== null) {
            $filters['program_id'] = ['value' => $programId];
        }

        if ($cohortId !== null) {
            $filters['cohort_id'] = ['value' => $cohortId];
        }

        $parameters = ['tenant' => $tenant];
        if ($filters !== []) {
            $parameters['filters'] = $filters;
        }

        return ApplicationResource::getUrl('index', $parameters, panel: 'sae');
    }

    /**
     * @param  array{total: int, accepted: int, pending: int, waitlisted: int, rejected: int}  $bucket
     */
    private static function incrementStatusCounters(array &$bucket, string $status, int $count): void
    {
        $bucket['total'] += $count;

        if ($status === ApplicationStatus::ACCEPTEE->value) {
            $bucket['accepted'] += $count;
        } elseif ($status === ApplicationStatus::SOUMISE->value || $status === ApplicationStatus::EN_EXAMEN->value) {
            $bucket['pending'] += $count;
        } elseif ($status === ApplicationStatus::EN_ATTENTE->value) {
            $bucket['waitlisted'] += $count;
        } elseif ($status === ApplicationStatus::REFUSEE->value) {
            $bucket['rejected'] += $count;
        }
    }

    /**
     * @return array{
     *     total_applications: int,
     *     programs_count: int,
     *     cohorts_count: int,
     *     program_options: array<int, string>,
     *     programs: list<never>,
     * }
     */
    private static function emptyDistribution(): array
    {
        return [
            'total_applications' => 0,
            'programs_count' => 0,
            'cohorts_count' => 0,
            'program_options' => [],
            'programs' => [],
        ];
    }
}
