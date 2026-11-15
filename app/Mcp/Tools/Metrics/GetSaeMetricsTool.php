<?php

namespace App\Mcp\Tools\Metrics;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_sae_metrics')]
#[Title('Statistiques et métriques d\'impact de la structure')]
#[Description('Renvoie les indicateurs clés de la SAE : total des programmes, cohortes actives, candidatures reçues et admises, startups accompagnées et heures de coaching délivrées.')]
class GetSaeMetricsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        // Programs: 2 queries → 1
        $programStats = Program::where('sae_id', $sae->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN is_active THEN 1 ELSE 0 END) as published')
            ->toBase()->first();

        $totalPrograms = (int) $programStats->total;
        $publishedPrograms = (int) $programStats->published;

        // Cohorts: 3 queries → 1
        $cohortStats = Cohort::where('sae_id', $sae->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'OUVERTE' THEN 1 ELSE 0 END) as open")
            ->selectRaw("SUM(CASE WHEN status = 'EN_COURS' THEN 1 ELSE 0 END) as ongoing")
            ->toBase()->first();

        $totalCohorts = (int) $cohortStats->total;
        $openCohorts = (int) $cohortStats->open;
        $ongoingCohorts = (int) $cohortStats->ongoing;

        // Applications: 3 queries → 1
        $applicationStats = Application::where('sae_id', $sae->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'ACCEPTEE' THEN 1 ELSE 0 END) as accepted")
            ->selectRaw("SUM(CASE WHEN status IN ('SOUMISE', 'EN_REVUE', 'EN_ATTENTE') THEN 1 ELSE 0 END) as pending")
            ->toBase()->first();

        $totalApplications = (int) $applicationStats->total;
        $acceptedApplications = (int) $applicationStats->accepted;
        $pendingApplications = (int) $applicationStats->pending;

        // Projects: 2 queries → 1
        $projectStats = Project::where('sae_id', $sae->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'ACTIF' THEN 1 ELSE 0 END) as active")
            ->toBase()->first();

        $totalProjects = (int) $projectStats->total;
        $activeProjects = (int) $projectStats->active;

        // Coaches: already a single query
        $totalCoaches = Coach::where('sae_id', $sae->id)->count();

        // Sessions: 2 queries → 1 (sum only for completed sessions)
        $sessionStats = CoachingSession::where('sae_id', $sae->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'REALISEE' THEN hours_delivered ELSE 0 END) as hours_delivered")
            ->toBase()->first();

        $totalSessions = (int) $sessionStats->total;
        $hoursDelivered = (float) ($sessionStats->hours_delivered ?? 0);

        return Response::structured([
            'sae' => [
                'id' => $sae->id,
                'name' => $sae->name,
                'code' => $sae->code,
                'country' => $sae->country,
                'city' => $sae->city,
            ],
            'programs' => [
                'total' => $totalPrograms,
                'published' => $publishedPrograms,
            ],
            'cohorts' => [
                'total' => $totalCohorts,
                'open' => $openCohorts,
                'ongoing' => $ongoingCohorts,
            ],
            'applications' => [
                'total' => $totalApplications,
                'accepted' => $acceptedApplications,
                'pending_review' => $pendingApplications,
                'acceptance_rate' => $totalApplications > 0
                    ? round(($acceptedApplications / $totalApplications) * 100, 1).'%'
                    : '0%',
            ],
            'startups' => [
                'total' => $totalProjects,
                'active' => $activeProjects,
            ],
            'coaching' => [
                'total_coaches' => $totalCoaches,
                'sessions_count' => $totalSessions,
                'hours_delivered' => $hoursDelivered,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
