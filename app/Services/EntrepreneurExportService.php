<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Models\Entrepreneur;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EntrepreneurExportService
{
    public function __construct(
        private readonly SaeCountryBreakdownService $countryBreakdownService,
    ) {}

    /**
     * Formate une ligne d'export pour un porteur de projet (fonction pure sur le modèle déjà chargé).
     *
     * @return list<int|string>
     */
    public function formatRow(Entrepreneur $entrepreneur): array
    {
        $nonDraftApplications = $entrepreneur->applications
            ->filter(fn ($application): bool => ($application->status instanceof ApplicationStatus
                ? $application->status->value
                : (string) $application->status) !== ApplicationStatus::BROUILLON->value);

        $projectsCount = (int) ($entrepreneur->projects_count ?? $entrepreneur->projects->count());
        $applicationsCount = $nonDraftApplications->count();
        $enrollmentsCount = (int) ($entrepreneur->enrollments_count ?? $entrepreneur->enrollments->count());

        $hasProject = $projectsCount > 0;
        $hasApplication = $applicationsCount > 0;
        $isEnrolled = $enrollmentsCount > 0;

        $country = $this->countryBreakdownService->resolveCountryForEntrepreneur(
            $entrepreneur,
            $nonDraftApplications,
        );

        $statusLabel = $entrepreneur->status instanceof EntrepreneurStatus
            ? ($entrepreneur->status->getLabel() ?? $entrepreneur->status->value)
            : (string) ($entrepreneur->status ?? '');

        $genderLabel = $entrepreneur->gender instanceof Gender
            ? ($entrepreneur->gender->getLabel() ?? $entrepreneur->gender->value)
            : (string) ($entrepreneur->gender ?? '');

        $enrolledCohorts = $entrepreneur->enrollments
            ->map(function ($enrollment): ?string {
                $cohortName = $enrollment->cohort?->name;
                $programName = $enrollment->cohort?->program?->name;

                if ($programName && $cohortName) {
                    return "{$programName} — {$cohortName}";
                }

                return $cohortName ?? $programName;
            })
            ->filter()
            ->unique()
            ->implode(' | ');

        return [
            trim("{$entrepreneur->first_name} {$entrepreneur->last_name}"),
            (string) ($entrepreneur->first_name ?? ''),
            (string) ($entrepreneur->last_name ?? ''),
            (string) ($entrepreneur->email ?? ''),
            (string) ($entrepreneur->phone ?? ''),
            $genderLabel,
            $country,
            (string) ($entrepreneur->territory ?? ''),
            $statusLabel,
            $hasProject ? 'Oui' : 'Non',
            $projectsCount,
            $hasApplication ? 'Oui' : 'Non',
            $applicationsCount,
            $isEnrolled ? 'Oui' : 'Non',
            $enrolledCohorts !== '' ? $enrolledCohorts : '—',
            $entrepreneur->created_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    /**
     * Exporte tous les porteurs de projet d'une SAE au format CSV compatible Excel (UTF-8 BOM, séparateur ;).
     */
    public function streamEntrepreneursCsv(?int $saeId, ?string $saeSlug = null): StreamedResponse
    {
        $slug = Str::slug((string) ($saeSlug ?: 'sae'));
        $filename = "porteurs-de-projet-{$slug}-".now()->format('Ymd-His').'.csv';

        $entrepreneurs = Entrepreneur::withoutGlobalScopes()
            ->when($saeId !== null, fn ($query) => $query->where('sae_id', $saeId))
            ->with([
                'applications' => fn ($query) => $query->withoutGlobalScopes(),
                'enrollments' => fn ($query) => $query->withoutGlobalScopes()->with('cohort.program'),
            ])
            ->withCount(['projects', 'enrollments'])
            ->orderByDesc('created_at')
            ->get();

        return response()->streamDownload(function () use ($entrepreneurs): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Nom complet',
                'Prénom',
                'Nom',
                'Email',
                'Téléphone',
                'Genre',
                'Pays',
                'Territoire / Ville',
                'Statut',
                'A un projet',
                'Nb projets',
                'A une candidature',
                'Nb candidatures',
                'Enrôlé',
                'Programmes / Cohortes enrôlés',
                'Date de création',
            ], ';');

            foreach ($entrepreneurs as $entrepreneur) {
                fputcsv($handle, $this->formatRow($entrepreneur), ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
