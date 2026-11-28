<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Entrepreneur;
use App\Models\Sae;
use App\Support\CountryCatalog;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SaeCountryBreakdownService
{
    /**
     * Résout le pays d'un entrepreneur :
     * 1. Champ `country` du compte entrepreneur
     * 2. Fallback sur les réponses de ses candidatures (la plus récente en premier)
     * 3. Sinon `Non renseigné`
     *
     * @param  Collection<int, Application>  $applications
     */
    public function resolveCountryForEntrepreneur(Entrepreneur $entrepreneur, Collection $applications): string
    {
        $fromProfile = CountryCatalog::normalize($entrepreneur->country);

        if ($fromProfile !== null) {
            return $fromProfile;
        }

        foreach ($applications->sortByDesc('id') as $application) {
            $fromApplication = CountryCatalog::extractCountryFromAnswers($application->answers);

            if ($fromApplication !== null) {
                return $fromApplication;
            }
        }

        return CountryCatalog::UNKNOWN_LABEL;
    }

    /**
     * Résout le pays d'une candidature (compte entrepreneur en priorité, puis réponses de la candidature).
     */
    public function resolveCountryForApplication(Application $application): string
    {
        $fromProfile = CountryCatalog::normalize($application->entrepreneur?->country);

        if ($fromProfile !== null) {
            return $fromProfile;
        }

        $fromApplication = CountryCatalog::extractCountryFromAnswers($application->answers);

        return $fromApplication ?? CountryCatalog::UNKNOWN_LABEL;
    }

    /**
     * Calcule la répartition géographique globale (tous les comptes, y compris sans projet)
     * et la répartition par appel (programme / cohorte) × pays pour les candidatures.
     *
     * @return array{
     *     summary: array{
     *         total_accounts: int,
     *         accounts_with_application: int,
     *         accounts_with_project: int,
     *         accounts_without_project: int,
     *         total_applications: int,
     *         total_calls: int
     *     },
     *     global_by_country: list<array{
     *         country: string,
     *         total_accounts: int,
     *         with_application: int,
     *         with_project: int,
     *         without_project: int,
     *         percentage: float
     *     }>,
     *     by_call_and_country: list<array{
     *         call_key: string,
     *         call_label: string,
     *         program_name: string,
     *         cohort_name: ?string,
     *         total_applications: int,
     *         countries: list<array{
     *             country: string,
     *             count: int,
     *             percentage: float
     *         }>
     *     }>
     * }
     */
    public function buildForSae(Sae $sae, ?int $programId = null, ?int $cohortId = null): array
    {
        $entrepreneurs = Entrepreneur::withoutGlobalScopes()
            ->where('sae_id', $sae->id)
            ->with([
                'applications' => fn ($query) => $query->withoutGlobalScopes()->where('status', '!=', ApplicationStatus::BROUILLON->value),
            ])
            ->withCount('projects')
            ->get();

        $totalAccounts = $entrepreneurs->count();
        $accountsWithApplication = 0;
        $accountsWithProject = 0;
        $accountsWithoutProject = 0;

        /** @var array<string, array{country: string, total_accounts: int, with_application: int, with_project: int, without_project: int}> $globalBuckets */
        $globalBuckets = [];

        foreach ($entrepreneurs as $entrepreneur) {
            $hasApplication = $entrepreneur->applications->isNotEmpty();
            $hasProject = (int) $entrepreneur->projects_count > 0;

            if ($hasApplication) {
                $accountsWithApplication++;
            }

            if ($hasProject) {
                $accountsWithProject++;
            } else {
                $accountsWithoutProject++;
            }

            $country = $this->resolveCountryForEntrepreneur($entrepreneur, $entrepreneur->applications);

            if (! isset($globalBuckets[$country])) {
                $globalBuckets[$country] = [
                    'country' => $country,
                    'total_accounts' => 0,
                    'with_application' => 0,
                    'with_project' => 0,
                    'without_project' => 0,
                ];
            }

            $globalBuckets[$country]['total_accounts']++;

            if ($hasApplication) {
                $globalBuckets[$country]['with_application']++;
            }

            if ($hasProject) {
                $globalBuckets[$country]['with_project']++;
            } else {
                $globalBuckets[$country]['without_project']++;
            }
        }

        $globalByCountry = array_values($globalBuckets);
        usort($globalByCountry, function (array $left, array $right): int {
            if ($left['country'] === CountryCatalog::UNKNOWN_LABEL && $right['country'] !== CountryCatalog::UNKNOWN_LABEL) {
                return 1;
            }
            if ($right['country'] === CountryCatalog::UNKNOWN_LABEL && $left['country'] !== CountryCatalog::UNKNOWN_LABEL) {
                return -1;
            }
            if ($left['total_accounts'] !== $right['total_accounts']) {
                return $right['total_accounts'] <=> $left['total_accounts'];
            }

            return strcmp($left['country'], $right['country']);
        });

        foreach ($globalByCountry as &$row) {
            $row['percentage'] = $totalAccounts > 0
                ? round(($row['total_accounts'] / $totalAccounts) * 100, 1)
                : 0.0;
        }
        unset($row);

        // Répartition par appel (programme / cohorte) et par pays pour les candidatures
        $applicationsQuery = Application::withoutGlobalScopes()
            ->where('sae_id', $sae->id)
            ->where('status', '!=', ApplicationStatus::BROUILLON->value)
            ->with(['entrepreneur', 'program', 'cohort']);

        if ($programId !== null) {
            $applicationsQuery->where('program_id', $programId);
        }

        if ($cohortId !== null) {
            $applicationsQuery->where('cohort_id', $cohortId);
        }

        $applications = $applicationsQuery->get();

        /** @var array<string, array{call_key: string, call_label: string, program_name: string, cohort_name: ?string, total_applications: int, country_counts: array<string, int>}> $callBuckets */
        $callBuckets = [];

        foreach ($applications as $application) {
            $programName = $application->program?->name ?? 'Appel sans programme';
            $cohortName = $application->cohort?->name;
            $callKey = ($application->program_id ?? 0).':'.($application->cohort_id ?? 0);
            $callLabel = $cohortName ? "{$programName} — {$cohortName}" : $programName;

            if (! isset($callBuckets[$callKey])) {
                $callBuckets[$callKey] = [
                    'call_key' => $callKey,
                    'call_label' => $callLabel,
                    'program_name' => $programName,
                    'cohort_name' => $cohortName,
                    'total_applications' => 0,
                    'country_counts' => [],
                ];
            }

            $country = $this->resolveCountryForApplication($application);
            $callBuckets[$callKey]['total_applications']++;
            $callBuckets[$callKey]['country_counts'][$country] = ($callBuckets[$callKey]['country_counts'][$country] ?? 0) + 1;
        }

        $byCallAndCountry = [];

        foreach ($callBuckets as $bucket) {
            $countries = [];
            foreach ($bucket['country_counts'] as $country => $count) {
                $countries[] = [
                    'country' => $country,
                    'count' => $count,
                    'percentage' => $bucket['total_applications'] > 0
                        ? round(($count / $bucket['total_applications']) * 100, 1)
                        : 0.0,
                ];
            }

            usort($countries, function (array $left, array $right): int {
                if ($left['country'] === CountryCatalog::UNKNOWN_LABEL && $right['country'] !== CountryCatalog::UNKNOWN_LABEL) {
                    return 1;
                }
                if ($right['country'] === CountryCatalog::UNKNOWN_LABEL && $left['country'] !== CountryCatalog::UNKNOWN_LABEL) {
                    return -1;
                }
                if ($left['count'] !== $right['count']) {
                    return $right['count'] <=> $left['count'];
                }

                return strcmp($left['country'], $right['country']);
            });

            $byCallAndCountry[] = [
                'call_key' => $bucket['call_key'],
                'call_label' => $bucket['call_label'],
                'program_name' => $bucket['program_name'],
                'cohort_name' => $bucket['cohort_name'],
                'total_applications' => $bucket['total_applications'],
                'countries' => $countries,
            ];
        }

        usort($byCallAndCountry, fn (array $a, array $b): int => $b['total_applications'] <=> $a['total_applications']);

        return [
            'summary' => [
                'total_accounts' => $totalAccounts,
                'accounts_with_application' => $accountsWithApplication,
                'accounts_with_project' => $accountsWithProject,
                'accounts_without_project' => $accountsWithoutProject,
                'total_applications' => $applications->count(),
                'total_calls' => count($byCallAndCountry),
            ],
            'global_by_country' => $globalByCountry,
            'by_call_and_country' => $byCallAndCountry,
        ];
    }

    /**
     * Exporte en CSV (compatible Excel, UTF-8 BOM, séparateur ;) la répartition globale et par appel.
     *
     * @param  array<string, mixed>  $breakdown
     */
    public function streamCsv(Sae $sae, array $breakdown): StreamedResponse
    {
        $filename = 'repartition-pays-appels-'.$sae->slug.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($sae, $breakdown): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['RÉPARTITION GÉOGRAPHIQUE PAR PAYS & PAR APPEL — HUBS AFRICA'], ';');
            fputcsv($handle, ['Structure / SAE', $sae->name], ';');
            fputcsv($handle, ['Date d\'extraction', now()->format('d/m/Y H:i:s')], ';');
            fputcsv($handle, ['Total comptes inscrits', $breakdown['summary']['total_accounts'] ?? 0], ';');
            fputcsv($handle, ['Dont avec candidature', $breakdown['summary']['accounts_with_application'] ?? 0], ';');
            fputcsv($handle, ['Dont sans projet', $breakdown['summary']['accounts_without_project'] ?? 0], ';');
            fputcsv($handle, ['Total candidatures', $breakdown['summary']['total_applications'] ?? 0], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, ['--- 1. RÉPARTITION GLOBALE PAR PAYS (TOUS LES COMPTES, Y COMPRIS SANS PROJET) ---'], ';');
            fputcsv($handle, ['Pays', 'Total comptes', 'Avec candidature', 'Avec projet', 'Sans projet', 'Part (%)'], ';');

            foreach ($breakdown['global_by_country'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['country'],
                    $row['total_accounts'],
                    $row['with_application'],
                    $row['with_project'],
                    $row['without_project'],
                    number_format((float) $row['percentage'], 1, ',', '').'%',
                ], ';');
            }

            fputcsv($handle, [], ';');
            fputcsv($handle, ['--- 2. RÉPARTITION PAR APPEL (PROGRAMME / COHORTE) ET PAR PAYS ---'], ';');
            fputcsv($handle, ['Appel (Programme / Cohorte)', 'Pays', 'Candidatures', 'Part de l\'appel (%)'], ';');

            foreach ($breakdown['by_call_and_country'] ?? [] as $call) {
                foreach ($call['countries'] as $countryRow) {
                    fputcsv($handle, [
                        $call['call_label'],
                        $countryRow['country'],
                        $countryRow['count'],
                        number_format((float) $countryRow['percentage'], 1, ',', '').'%',
                    ], ';');
                }
                fputcsv($handle, ["TOTAL — {$call['call_label']}", '', $call['total_applications'], '100,0%'], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
