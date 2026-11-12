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

        $totalPrograms = Program::where('sae_id', $sae->id)->count();
        $publishedPrograms = Program::where('sae_id', $sae->id)->where('is_published', true)->count();

        $totalCohorts = Cohort::where('sae_id', $sae->id)->count();
        $openCohorts = Cohort::where('sae_id', $sae->id)->where('status', 'OUVERTE')->count();
        $ongoingCohorts = Cohort::where('sae_id', $sae->id)->where('status', 'EN_COURS')->count();

        $totalApplications = Application::where('sae_id', $sae->id)->count();
        $acceptedApplications = Application::where('sae_id', $sae->id)->where('status', 'ACCEPTEE')->count();
        $pendingApplications = Application::where('sae_id', $sae->id)->whereIn('status', ['SOUMISE', 'EN_REVUE', 'EN_ATTENTE'])->count();

        $totalProjects = Project::where('sae_id', $sae->id)->count();
        $activeProjects = Project::where('sae_id', $sae->id)->where('status', 'ACTIF')->count();

        $totalCoaches = Coach::where('sae_id', $sae->id)->count();
        $totalSessions = CoachingSession::where('sae_id', $sae->id)->count();
        $hoursDelivered = (float) CoachingSession::where('sae_id', $sae->id)
            ->where('status', 'REALISEE')
            ->sum('hours_delivered');

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
