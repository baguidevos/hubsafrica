<?php

namespace App\Http\Controllers\Sae;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Sae;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportPrintController extends Controller
{
    /**
     * Vérifie que l'utilisateur connecté a le droit d'accéder aux rapports de cette SAE.
     */
    protected function authorizeTenantAccess(Sae $tenant): void
    {
        $user = auth()->user();

        if (! $user || ! $user->canAccessTenant($tenant)) {
            abort(403, 'Accès non autorisé aux rapports de cette structure.');
        }
    }

    /**
     * Construit le tableau des filtres résolus avec noms lisibles.
     */
    protected function resolveFilters(Sae $tenant, Request $request): array
    {
        $programId = $request->query('program_id') ?? $request->query('program', 'all');
        $cohortId = $request->query('cohort_id') ?? $request->query('cohort', 'all');

        $programName = 'Tous les programmes';
        if ($programId && $programId !== 'all') {
            $p = Program::withoutGlobalScopes()->where('sae_id', $tenant->id)->find($programId);
            if ($p) {
                $programName = $p->name;
            }
        }

        $cohortName = 'Toutes les cohortes';
        if ($cohortId && $cohortId !== 'all') {
            $c = Cohort::withoutGlobalScopes()->where('sae_id', $tenant->id)->find($cohortId);
            if ($c) {
                $cohortName = $c->name;
            }
        }

        return [
            'program_id' => $programId,
            'cohort_id' => $cohortId,
            'program_name' => $programName,
            'cohort_name' => $cohortName,
        ];
    }

    /**
     * Vue officielle d'impression et d'enregistrement PDF du rapport d'impact.
     */
    public function impactPrint(Sae $tenant, Request $request, ReportExportService $exportService): Response
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);
        $branding = $exportService->getTenantBranding($tenant);
        $indicators = $exportService->getImpactIndicators($tenant, $filters);

        return response()->view('reports.print-impact', [
            'tenant' => $tenant,
            'branding' => $branding,
            'filters' => $filters,
            'indicators' => $indicators,
            'author' => auth()->user()?->name ?? 'Direction SAE',
        ]);
    }

    /**
     * Téléchargement autonome du rapport d'impact (.html).
     */
    public function impactDownload(Sae $tenant, Request $request, ReportExportService $exportService): StreamedResponse
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);

        return $exportService->downloadImpactHtml($tenant, $filters, auth()->user()?->name);
    }

    /**
     * Export tabulaire CSV des indicateurs d'impact.
     */
    public function impactCsv(Sae $tenant, Request $request, ReportExportService $exportService): StreamedResponse
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);
        $indicators = $exportService->getImpactIndicators($tenant, $filters);

        return $exportService->exportIndicatorsCsv($tenant, $indicators, $filters);
    }

    /**
     * Vue officielle d'impression et d'enregistrement PDF du diagnostic stratégique.
     */
    public function analyticsPrint(Sae $tenant, Request $request, ReportExportService $exportService): Response
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);
        $branding = $exportService->getTenantBranding($tenant);
        $data = $exportService->getAnalyticsData($tenant, $filters);

        return response()->view('reports.print-analytics', [
            'tenant' => $tenant,
            'branding' => $branding,
            'filters' => $filters,
            'data' => $data,
            'author' => auth()->user()?->name ?? 'Direction SAE',
        ]);
    }

    /**
     * Téléchargement autonome du diagnostic analytique (.html).
     */
    public function analyticsDownload(Sae $tenant, Request $request, ReportExportService $exportService): StreamedResponse
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);

        return $exportService->downloadAnalyticsHtml($tenant, $filters, auth()->user()?->name);
    }

    /**
     * Export tabulaire CSV de la performance du portefeuille.
     */
    public function analyticsCsv(Sae $tenant, Request $request, ReportExportService $exportService): StreamedResponse
    {
        $this->authorizeTenantAccess($tenant);

        $filters = $this->resolveFilters($tenant, $request);
        $data = $exportService->getAnalyticsData($tenant, $filters);

        return $exportService->exportAnalyticsCsv($tenant, $data, $filters);
    }
}
