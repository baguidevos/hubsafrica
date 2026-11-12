<?php

namespace App\Mcp\Tools\Coaching;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Project;
use App\Models\TimelineEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('assign_coach_to_project')]
#[Title('Affecter un coach à une startup')]
#[Description('Assigne un coach ou mentor à un projet accompagné, définit son rôle (Coach principal, Mentor technique, etc.) et l\'enregistre dans la timeline.')]
class AssignCoachToProjectTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'coach_id' => 'required|integer',
            'project_id' => 'required|integer',
            'role' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        /** @var Coach|null $coach */
        $coach = Coach::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['coach_id'])
            ->first();

        if (! $coach) {
            return Response::error("Coach #{$validated['coach_id']} introuvable ou non rattaché à cette SAE.");
        }

        /** @var Project|null $project */
        $project = Project::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['project_id'])
            ->first();

        if (! $project) {
            return Response::error("Projet #{$validated['project_id']} introuvable ou non rattaché à cette SAE.");
        }

        $assignment = CoachAssignment::updateOrCreate(
            [
                'sae_id' => $sae->id,
                'coach_id' => $coach->id,
                'project_id' => $project->id,
            ],
            [
                'role' => $validated['role'] ?? 'Coach Principal',
                'status' => 'ACTIF',
                'assigned_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        TimelineEvent::create([
            'sae_id' => $sae->id,
            'project_id' => $project->id,
            'entrepreneur_id' => $project->entrepreneur_id,
            'title' => "Affectation du Coach : {$coach->name}",
            'description' => "Le coach {$coach->name} a été assigné au projet ({$assignment->role}).".(! empty($validated['notes']) ? " Note : {$validated['notes']}" : ''),
            'type' => 'COACHING',
            'icon' => 'heroicon-m-user-group',
        ]);

        $this->logAudit(
            action: 'ASSIGN_COACH',
            entity: 'CoachAssignment',
            entityId: (string) $assignment->id,
            details: [
                'coach_id' => $coach->id,
                'coach_name' => $coach->name,
                'project_id' => $project->id,
                'project_name' => $project->name,
                'role' => $assignment->role,
            ]
        );

        return Response::text("Le coach {$coach->name} a été assigné avec succès au projet « {$project->name} » (Rôle : {$assignment->role}).");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'coach_id' => $schema->integer()
                ->description('Identifiant numérique unique du coach')
                ->required(),
            'project_id' => $schema->integer()
                ->description('Identifiant numérique unique de la startup / projet')
                ->required(),
            'role' => $schema->string()
                ->description('Rôle du coach (ex: Coach Principal, Mentor Financier, Expert Technique, etc.)')
                ->default('Coach Principal'),
            'notes' => $schema->string()
                ->description('Instructions ou objectifs fixés pour cet accompagnement'),
        ];
    }
}
