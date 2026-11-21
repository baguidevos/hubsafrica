<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Enums\DeliverableSubmissionStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\FundingStatus;
use App\Enums\FundingType;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\CoachingSession;
use App\Models\DeliverableSubmission;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\FundingRecord;
use App\Models\Indicator;
use App\Models\Project;
use App\Models\Sae;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    /**
     * Récupère les données d'image et le logo du tenant (base64 ou URL publique)
     * ainsi que le logo de certification Hubs Africa.
     *
     * @return array{src: string|null, is_base64: bool, initials: string, name: string, hub_logo: string|null}
     */
    public function getTenantBranding(?Sae $tenant): array
    {
        $name = $tenant?->name ?? 'Hubs Africa SAE';
        $initials = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name) ?: 'SA', 0, 2));
        $hubLogo = $this->getFallbackHubAfricaLogoBase64();

        if (! $tenant) {
            return [
                'src' => null,
                'is_base64' => false,
                'initials' => $initials,
                'name' => $name,
                'hub_logo' => $hubLogo,
            ];
        }

        $tenantLogoSrc = null;
        $isBase64 = false;

        // 1. Logo personnalisé téléversé par le tenant
        if (! blank($tenant->logo)) {
            try {
                if (str_starts_with($tenant->logo, 'http://') || str_starts_with($tenant->logo, 'https://')) {
                    $tenantLogoSrc = $tenant->logo;
                } elseif (Storage::disk('public')->exists($tenant->logo)) {
                    $fileContent = Storage::disk('public')->get($tenant->logo);
                    $mime = Storage::disk('public')->mimeType($tenant->logo) ?: 'image/png';
                    $tenantLogoSrc = 'data:'.$mime.';base64,'.base64_encode($fileContent);
                    $isBase64 = true;
                } else {
                    $avatarUrl = $tenant->getFilamentAvatarUrl();
                    if ($avatarUrl) {
                        $tenantLogoSrc = $avatarUrl;
                    }
                }
            } catch (\Throwable) {
                // Ignore fallback
            }
        }

        return [
            'src' => $tenantLogoSrc,
            'is_base64' => $isBase64,
            'initials' => $initials,
            'name' => $name,
            'hub_logo' => $hubLogo,
        ];
    }

    /**
     * Lit et encode en base64 le logo officiel de l'application Hubs Africa.
     */
    public function getFallbackHubAfricaLogoBase64(): ?string
    {
        $paths = [
            public_path('images/logo2.png'),
            public_path('images/logo2.png'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                try {
                    $data = file_get_contents($path);
                    if ($data !== false) {
                        return 'data:image/png;base64,'.base64_encode($data);
                    }
                } catch (\Throwable) {
                    // continue
                }
            }
        }

        return null;
    }

    /**
     * Calcule ou extrait la liste des indicateurs S&E d'impact pour une structure donnée.
     */
    public function getImpactIndicators(Sae $tenant, array $filters = []): array
    {
        $query = Indicator::withoutGlobalScopes()->where('sae_id', $tenant->id);
        $indicators = $query->get();

        if ($indicators->isNotEmpty()) {
            return $indicators->map(fn ($i) => [
                'name' => $i->name,
                'category' => $i->category instanceof \BackedEnum ? $i->category->value : ($i->category ?: 'GÉNÉRAL'),
                'formula' => $i->formula ?: '—',
                'current' => (float) $i->current,
                'unit' => $i->unit ?: '',
                'target' => (float) $i->target,
                'status' => $i->status instanceof \BackedEnum ? $i->status->value : ($i->status ?: 'ATTEINT'),
            ])->toArray();
        }

        // Calculs dynamiques standards S&E si la table manuelle est vierge pour ce tenant
        $filteredProjectIds = $this->resolveFilteredProjectIds($tenant, $filters);

        $appQuery = Application::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if (! empty($filters['program_id']) && $filters['program_id'] !== 'all') {
            $appQuery->where('program_id', $filters['program_id']);
        }
        if (! empty($filters['cohort_id']) && $filters['cohort_id'] !== 'all') {
            $appQuery->where('cohort_id', $filters['cohort_id']);
        }

        $examinees = (clone $appQuery)->whereNotNull('reviewed_at')->count();
        if ($examinees === 0) {
            $examinees = (clone $appQuery)->whereIn('status', [
                ApplicationStatus::ACCEPTEE->value,
                ApplicationStatus::REFUSEE->value,
                ApplicationStatus::EN_ATTENTE->value,
                ApplicationStatus::REORIENTEE->value,
            ])->count();
        }
        $admises = (clone $appQuery)->where('status', ApplicationStatus::ACCEPTEE->value)->count();
        $tauxAdmission = $examinees > 0 ? min(100, round(($admises / $examinees) * 100, 1)) : 0.0;

        $entQuery = Entrepreneur::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->whereIn('status', [EntrepreneurStatus::ACTIVE->value, EntrepreneurStatus::DIPLOME->value])
            ->where(function ($q) use ($filteredProjectIds) {
                if ($filteredProjectIds !== null) {
                    $q->whereHas('projects', fn ($pq) => $pq->withoutGlobalScopes()->whereIn('projects.id', $filteredProjectIds));
                } else {
                    $q->whereHas('projects')->orWhereHas('enrollments');
                }
            });

        $totalEnt = (clone $entQuery)->count();
        $femmes = (clone $entQuery)->where('gender', 'F')->count();
        $tauxGenre = $totalEnt > 0 ? round(($femmes / $totalEnt) * 100, 1) : 0.0;

        $subQuery = DeliverableSubmission::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if ($filteredProjectIds !== null) {
            $subQuery->whereIn('project_id', $filteredProjectIds);
        }
        $livrablesEvalues = (clone $subQuery)->whereIn('status', [
            DeliverableSubmissionStatus::VALIDEE->value,
            DeliverableSubmissionStatus::VALIDE->value,
            DeliverableSubmissionStatus::REJETEE->value,
            DeliverableSubmissionStatus::NON_VALIDE->value,
            DeliverableSubmissionStatus::CORRECTION_DEMANDEE->value,
        ])->count();
        $livrablesValides = (clone $subQuery)->whereIn('status', [
            DeliverableSubmissionStatus::VALIDEE->value,
            DeliverableSubmissionStatus::VALIDE->value,
        ])->count();
        $tauxValidation = $livrablesEvalues > 0 ? round(($livrablesValides / $livrablesEvalues) * 100, 1) : 0.0;

        $sessionQuery = CoachingSession::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if ($filteredProjectIds !== null) {
            $sessionQuery->whereIn('project_id', $filteredProjectIds);
        }
        $seancesPrevues = (clone $sessionQuery)->where('status', '!=', CoachingSessionStatus::ANNULEE->value)->count();
        $seancesRealisees = (clone $sessionQuery)->where('status', CoachingSessionStatus::REALISEE->value)->count();
        $tauxSeances = $seancesPrevues > 0 ? round(($seancesRealisees / $seancesPrevues) * 100, 1) : 0.0;

        $projectQuery = Project::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->whereIn('status', [ProjectStatus::ACTIF->value, ProjectStatus::DIPLOME->value]);
        if ($filteredProjectIds !== null) {
            $projectQuery->whereIn('id', $filteredProjectIds);
        }
        $emploisDirects = (int) $projectQuery->sum('team_size');

        $fondsQuery = FundingRecord::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->whereIn('status', [FundingStatus::DECAISSE->value, FundingStatus::INVESTI->value]);
        if ($filteredProjectIds !== null) {
            $fondsQuery->whereIn('project_id', $filteredProjectIds);
        }
        $fonds = (float) $fondsQuery->selectRaw('SUM(COALESCE(NULLIF(amount_obtained, 0), amount_invested, 0)) as total')->value('total');

        return [
            [
                'name' => 'Taux d\'admission au programme',
                'category' => 'GOUVERNANCE',
                'formula' => 'Candidatures admises / Décisions rendues × 100',
                'current' => $tauxAdmission,
                'unit' => '%',
                'target' => 30.0,
                'status' => $examinees === 0 ? 'NON_MESURABLE' : ($tauxAdmission <= 35 ? 'ATTEINT' : 'VIGILANCE'),
            ],
            [
                'name' => 'Taux de féminisation (Parité genre)',
                'category' => 'INCLUSION',
                'formula' => 'Entrepreneures femmes / Total des porteurs accompagnés × 100',
                'current' => $tauxGenre,
                'unit' => '%',
                'target' => 40.0,
                'status' => $totalEnt === 0 ? 'NON_MESURABLE' : ($tauxGenre >= 40 ? 'ATTEINT' : 'NON_ATTEINT'),
            ],
            [
                'name' => 'Taux de conformité des livrables',
                'category' => 'ACCOMPAGNEMENT',
                'formula' => 'Livrables validés / Livrables évalués × 100',
                'current' => $tauxValidation,
                'unit' => '%',
                'target' => 80.0,
                'status' => $livrablesEvalues === 0 ? 'NON_MESURABLE' : ($tauxValidation >= 75 ? 'ATTEINT' : 'VIGILANCE'),
            ],
            [
                'name' => 'Taux de réalisation des séances de coaching',
                'category' => 'ACCOMPAGNEMENT',
                'formula' => 'Séances réalisées / Séances planifiées (hors annulées) × 100',
                'current' => $tauxSeances,
                'unit' => '%',
                'target' => 80.0,
                'status' => $seancesPrevues === 0 ? 'NON_MESURABLE' : ($tauxSeances >= 75 ? 'ATTEINT' : 'VIGILANCE'),
            ],
            [
                'name' => 'Emplois directs portés (taille d\'équipe)',
                'category' => 'IMPACT',
                'formula' => 'Somme des membres d\'équipe sur projets actifs et diplômés',
                'current' => $emploisDirects,
                'unit' => 'emplois',
                'target' => 50.0,
                'status' => $emploisDirects > 0 ? 'ATTEINT' : 'VIGILANCE',
            ],
            [
                'name' => 'Volume de financements décaissés',
                'category' => 'FINANCEMENT',
                'formula' => 'Somme des montants décaissés & investis',
                'current' => $fonds,
                'unit' => 'FCFA',
                'target' => 50000000.0,
                'status' => $fonds > 0 ? 'ATTEINT' : 'VIGILANCE',
            ],
        ];
    }

    /**
     * Extrait les métriques consolidées du centre d'analyse pour une SAE.
     */
    public function getAnalyticsData(Sae $tenant, array $filters = []): array
    {
        $filteredProjectIds = $this->resolveFilteredProjectIds($tenant, $filters);

        $appQuery = Application::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if (! empty($filters['program_id']) && $filters['program_id'] !== 'all') {
            $appQuery->where('program_id', $filters['program_id']);
        }
        if (! empty($filters['cohort_id']) && $filters['cohort_id'] !== 'all') {
            $appQuery->where('cohort_id', $filters['cohort_id']);
        }

        $deposees = (clone $appQuery)->where('status', '!=', ApplicationStatus::BROUILLON->value)->count();
        $examinees = (clone $appQuery)->whereNotNull('reviewed_at')->count();
        if ($examinees === 0) {
            $examinees = (clone $appQuery)->whereIn('status', [
                ApplicationStatus::ACCEPTEE->value,
                ApplicationStatus::REFUSEE->value,
                ApplicationStatus::EN_ATTENTE->value,
                ApplicationStatus::REORIENTEE->value,
            ])->count();
        }
        $acceptees = (clone $appQuery)->where('status', ApplicationStatus::ACCEPTEE->value)->count();

        $enrollmentQuery = Enrollment::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->where('status', 'ACTIF');
        if (! empty($filters['cohort_id']) && $filters['cohort_id'] !== 'all') {
            $enrollmentQuery->where('cohort_id', $filters['cohort_id']);
        } elseif (! empty($filters['program_id']) && $filters['program_id'] !== 'all') {
            $enrollmentQuery->whereHas('cohort', fn ($q) => $q->withoutGlobalScopes()->where('program_id', $filters['program_id']));
        }
        $integrees = $enrollmentQuery->count();

        $funnel = [
            ['stage' => 'Candidatures déposées', 'count' => $deposees, 'pct' => $deposees > 0 ? 100 : 0],
            ['stage' => 'Candidatures examinées', 'count' => $examinees, 'pct' => $deposees > 0 ? min(100, round(($examinees / $deposees) * 100)) : 0],
            ['stage' => 'Candidatures admises', 'count' => $acceptees, 'pct' => $deposees > 0 ? min(100, round(($acceptees / $deposees) * 100)) : 0],
            ['stage' => 'Intégrés en cohorte', 'count' => $integrees, 'pct' => $deposees > 0 ? min(100, round(($integrees / $deposees) * 100)) : 0],
        ];

        // Démographie (uniquement porteurs actifs/diplômés avec projet ou enrôlement)
        $entQuery = Entrepreneur::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->whereIn('status', [EntrepreneurStatus::ACTIVE->value, EntrepreneurStatus::DIPLOME->value])
            ->where(function ($q) use ($filteredProjectIds) {
                if ($filteredProjectIds !== null) {
                    $q->whereHas('projects', fn ($pq) => $pq->withoutGlobalScopes()->whereIn('projects.id', $filteredProjectIds));
                } else {
                    $q->whereHas('projects')->orWhereHas('enrollments');
                }
            });

        $totalEnt = (clone $entQuery)->count();
        $femmes = (clone $entQuery)->where('gender', 'F')->count();
        $hommes = (clone $entQuery)->where('gender', 'M')->count();

        $pctFemmes = $totalEnt > 0 ? round(($femmes / $totalEnt) * 100) : 0;
        $pctHommes = $totalEnt > 0 ? round(($hommes / $totalEnt) * 100) : 0;

        $territoires = (clone $entQuery)
            ->whereNotNull('territory')
            ->where('territory', '!=', '')
            ->selectRaw('territory, count(*) as count')
            ->groupBy('territory')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $niveauxEtudes = (clone $entQuery)
            ->whereNotNull('education_level')
            ->where('education_level', '!=', '')
            ->selectRaw('education_level, count(*) as count')
            ->groupBy('education_level')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Accompagnement (uniquement séances réalisées)
        $sessionQuery = CoachingSession::withoutGlobalScopes()
            ->where('sae_id', $tenant->id)
            ->where('status', CoachingSessionStatus::REALISEE->value);
        if ($filteredProjectIds !== null) {
            $sessionQuery->whereIn('project_id', $filteredProjectIds);
        }

        $totalSessions = (clone $sessionQuery)->count();
        $totalHeures = round((float) (clone $sessionQuery)
            ->selectRaw('SUM(COALESCE(hours_delivered, duration / 60.0)) as total_hours')
            ->value('total_hours'), 1);

        $typesSeance = (clone $sessionQuery)
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->get()
            ->mapWithKeys(function ($r) {
                $key = $r->type instanceof CoachingSessionType ? $r->type->getLabel() : (string) $r->type;

                return [$key => $r->count];
            });

        // Financement (décaissés/investis réels + accordés séparément)
        $fundingBaseQuery = FundingRecord::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if ($filteredProjectIds !== null) {
            $fundingBaseQuery->whereIn('project_id', $filteredProjectIds);
        }

        $financements = (clone $fundingBaseQuery)
            ->selectRaw('type, SUM(COALESCE(NULLIF(amount_obtained, 0), amount_invested, 0)) as total')
            ->whereIn('status', [FundingStatus::DECAISSE->value, FundingStatus::INVESTI->value])
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();

        $totalFinancement = (float) (clone $fundingBaseQuery)
            ->whereIn('status', [FundingStatus::DECAISSE->value, FundingStatus::INVESTI->value])
            ->selectRaw('SUM(COALESCE(NULLIF(amount_obtained, 0), amount_invested, 0)) as total')
            ->value('total');

        $totalFinancementAccorde = (float) (clone $fundingBaseQuery)
            ->where('status', FundingStatus::ACCORDE->value)
            ->sum('amount_obtained');

        $projectStatusQuery = Project::withoutGlobalScopes()->where('sae_id', $tenant->id);
        if ($filteredProjectIds !== null) {
            $projectStatusQuery->whereIn('id', $filteredProjectIds);
        }

        $statutsProjets = $projectStatusQuery
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->orderByDesc('count')
            ->get();

        return [
            'funnel' => $funnel,
            'femmes' => $femmes,
            'hommes' => $hommes,
            'pctFemmes' => $pctFemmes,
            'pctHommes' => $pctHommes,
            'territoires' => $territoires,
            'niveauxEtudes' => $niveauxEtudes,
            'totalSessions' => $totalSessions,
            'totalHeures' => $totalHeures,
            'typesSeance' => $typesSeance,
            'financements' => $financements,
            'totalFinancement' => $totalFinancement,
            'totalFinancementAccorde' => $totalFinancementAccorde,
            'statutsProjets' => $statutsProjets,
        ];
    }

    /**
     * Retourne les IDs de projets correspondant aux filtres programme/cohorte, ou null si aucun filtre actif.
     *
     * @return array<int, int>|null
     */
    protected function resolveFilteredProjectIds(Sae $tenant, array $filters): ?array
    {
        $cohortId = $filters['cohort_id'] ?? 'all';
        $programId = $filters['program_id'] ?? 'all';

        if (! empty($cohortId) && $cohortId !== 'all') {
            return Enrollment::withoutGlobalScopes()
                ->where('sae_id', $tenant->id)
                ->where('cohort_id', $cohortId)
                ->pluck('project_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        if (! empty($programId) && $programId !== 'all') {
            return Enrollment::withoutGlobalScopes()
                ->where('sae_id', $tenant->id)
                ->whereHas('cohort', fn ($q) => $q->withoutGlobalScopes()->where('program_id', $programId))
                ->pluck('project_id')
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return null;
    }

    /**
     * Génère la chaîne HTML complète pour le rapport d'impact.
     */
    public function renderImpactHtml(Sae $tenant, array $filters = [], ?string $author = null): string
    {
        $branding = $this->getTenantBranding($tenant);
        $indicators = $this->getImpactIndicators($tenant, $filters);

        return view('reports.print-impact', [
            'tenant' => $tenant,
            'branding' => $branding,
            'filters' => $filters,
            'indicators' => $indicators,
            'author' => $author ?? (auth()->user()?->name ?? 'Coordinateur SAE'),
        ])->render();
    }

    /**
     * Génère la chaîne HTML complète pour le diagnostic analytique.
     */
    public function renderAnalyticsHtml(Sae $tenant, array $filters = [], ?string $author = null): string
    {
        $branding = $this->getTenantBranding($tenant);
        $data = $this->getAnalyticsData($tenant, $filters);

        return view('reports.print-analytics', [
            'tenant' => $tenant,
            'branding' => $branding,
            'filters' => $filters,
            'data' => $data,
            'author' => $author ?? (auth()->user()?->name ?? 'Coordinateur SAE'),
        ])->render();
    }

    /**
     * Téléchargement autonome du rapport d'impact au format fichier .html.
     */
    public function downloadImpactHtml(Sae $tenant, array $filters = [], ?string $author = null): StreamedResponse
    {
        $filename = 'rapport_impact_'.$tenant->slug.'_'.now()->format('Ymd_His').'.html';
        $html = $this->renderImpactHtml($tenant, $filters, $author);

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Téléchargement autonome du rapport analytique au format fichier .html.
     */
    public function downloadAnalyticsHtml(Sae $tenant, array $filters = [], ?string $author = null): StreamedResponse
    {
        $filename = 'rapport_performance_'.$tenant->slug.'_'.now()->format('Ymd_His').'.html';
        $html = $this->renderAnalyticsHtml($tenant, $filters, $author);

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $filename, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Exporte un tableau d'indicateurs au format CSV optimisé Excel (UTF-8 avec BOM).
     */
    public function exportIndicatorsCsv(Sae $tenant, array $data, array $filters = []): StreamedResponse
    {
        $filename = 'rapport_indicateurs_impact_'.$tenant->slug.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($tenant, $data, $filters) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour ouverture directe sans altération sous Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // En-tête institutionnel
            fputcsv($handle, ['RAPPORT D\'IMPACT & SUIVI-ÉVALUATION (M&E) — HUBS AFRICA']);
            fputcsv($handle, ['Structure / SAE', $tenant->name]);
            fputcsv($handle, ['Email', $tenant->email ?? '—']);
            fputcsv($handle, ['Téléphone', $tenant->phone ?? '—']);
            fputcsv($handle, ['Date d\'extraction', now()->format('d/m/Y H:i:s')]);
            fputcsv($handle, ['Programme sélectionné', $filters['program_name'] ?? 'Tous les programmes']);
            fputcsv($handle, ['Cohorte sélectionnée', $filters['cohort_name'] ?? 'Toutes les cohortes']);
            fputcsv($handle, []);

            // Colonnes du rapport
            fputcsv($handle, [
                'Indicateur',
                'Catégorie',
                'Formule de calcul',
                'Valeur actuelle',
                'Unité',
                'Valeur cible',
                'Statut M&E',
            ]);

            foreach ($data as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['category'],
                    $row['formula'],
                    $row['current'],
                    $row['unit'],
                    $row['target'],
                    $row['status'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Exporte les données du centre d'analyse au format CSV optimisé Excel.
     */
    public function exportAnalyticsCsv(Sae $tenant, array $data, array $filters = []): StreamedResponse
    {
        $filename = 'rapport_performance_portefeuille_'.$tenant->slug.'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($tenant, $data, $filters) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // En-tête institutionnel
            fputcsv($handle, ['RAPPORT DE PERFORMANCE DU PORTEFEUILLE — CENTRE D\'ANALYSE']);
            fputcsv($handle, ['Structure / SAE', $tenant->name]);
            fputcsv($handle, ['Date d\'extraction', now()->format('d/m/Y H:i:s')]);
            fputcsv($handle, ['Programme', $filters['program_name'] ?? 'Tous']);
            fputcsv($handle, ['Cohorte', $filters['cohort_name'] ?? 'Toutes']);
            fputcsv($handle, []);

            // 1. Entonnoir de sélection
            fputcsv($handle, ['--- ENTONNOIR DE SÉLECTION & CONVERSION ---']);
            fputcsv($handle, ['Étape', 'Nombre', 'Taux de conversion (%)']);
            foreach ($data['funnel'] ?? [] as $step) {
                fputcsv($handle, [$step['stage'], $step['count'], $step['pct'].'%']);
            }
            fputcsv($handle, []);

            // 2. Parité & Démographie
            fputcsv($handle, ['--- PARITÉ GENRE & INCLUSION ---']);
            fputcsv($handle, ['Indicateur', 'Valeur', 'Pourcentage']);
            fputcsv($handle, ['Femmes entrepreneures', $data['femmes'] ?? 0, ($data['pctFemmes'] ?? 0).'%']);
            fputcsv($handle, ['Hommes entrepreneurs', $data['hommes'] ?? 0, ($data['pctHommes'] ?? 0).'%']);
            fputcsv($handle, []);

            // 3. Accompagnement
            fputcsv($handle, ['--- INTENSITÉ D\'ACCOMPAGNEMENT ---']);
            fputcsv($handle, ['Métrique', 'Total']);
            fputcsv($handle, ['Nombre total de séances', $data['totalSessions'] ?? 0]);
            fputcsv($handle, ['Volume horaire délivré', ($data['totalHeures'] ?? 0).' heures']);
            foreach ($data['typesSeance'] ?? [] as $type => $count) {
                fputcsv($handle, ["Format : {$type}", $count]);
            }
            fputcsv($handle, []);

            // 4. Financement
            fputcsv($handle, ['--- FINANCEMENTS MOBILISÉS (FCFA) ---']);
            fputcsv($handle, ['Type de financement', 'Montant']);
            foreach ($data['financements'] ?? [] as $f) {
                $typeLabel = $f->type instanceof FundingType ? $f->type->getLabel() : (string) $f->type;
                fputcsv($handle, [$typeLabel, $f->total]);
            }
            fputcsv($handle, ['TOTAL FINANCEMENTS', $data['totalFinancement'] ?? 0]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
