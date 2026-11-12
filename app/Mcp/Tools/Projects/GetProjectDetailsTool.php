<?php

namespace App\Mcp\Tools\Projects;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Project;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_project_details')]
#[Title('Fiche détaillée d\'une startup / projet')]
#[Description('Récupère les informations complètes d\'un projet : proposition de valeur, pitch, porteur, cohorte active, coachs assignés et historique des niveaux.')]
class GetProjectDetailsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'project_id' => 'required|integer',
        ]);

        /** @var Project|null $project */
        $project = Project::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['project_id'])
            ->with([
                'currentLevel',
                'initialLevel',
                'entrepreneur.user',
                'cohorts',
                'coachAssignments.coach.user',
                'levelHistories.level',
            ])
            ->first();

        if (! $project) {
            return Response::error("Projet #{$validated['project_id']} introuvable ou non rattaché à cette SAE.");
        }

        $coaches = $project->coachAssignments->map(fn ($assign) => [
            'assignment_id' => $assign->id,
            'coach_id' => $assign->coach_id,
            'coach_name' => $assign->coach?->user?->name,
            'coach_email' => $assign->coach?->user?->email,
            'role' => $assign->role,
            'status' => $assign->status,
        ])->all();

        $cohorts = $project->cohorts->map(fn ($cohort) => [
            'id' => $cohort->id,
            'name' => $cohort->name,
            'status' => $cohort->status?->value ?? (string) $cohort->status,
            'start_date' => $cohort->start_date?->toDateString(),
            'end_date' => $cohort->end_date?->toDateString(),
        ])->all();

        $history = $project->levelHistories->map(fn ($h) => [
            'level_name' => $h->level?->name,
            'entered_at' => $h->entered_at?->toIso8601String(),
            'notes' => $h->notes,
        ])->all();

        return Response::structured([
            'id' => $project->id,
            'name' => $project->name,
            'status' => $project->status?->value ?? (string) $project->status,
            'sector' => $project->sector,
            'territory' => $project->territory,
            'team_size' => $project->team_size,
            'description' => $project->description,
            'problem' => $project->problem,
            'solution' => $project->solution,
            'value_proposition' => $project->value_proposition,
            'target' => $project->target,
            'market' => $project->market,
            'pitch' => $project->pitch,
            'current_level' => $project->currentLevel?->name,
            'initial_level' => $project->initialLevel?->name,
            'entrepreneur' => [
                'id' => $project->entrepreneur?->id,
                'name' => $project->entrepreneur?->user?->name,
                'email' => $project->entrepreneur?->user?->email,
                'phone' => $project->entrepreneur?->phone,
                'city' => $project->entrepreneur?->city,
                'country' => $project->entrepreneur?->country,
            ],
            'assigned_coaches' => $coaches,
            'cohorts' => $cohorts,
            'level_history' => $history,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Identifiant numérique unique du projet')
                ->required(),
        ];
    }
}
