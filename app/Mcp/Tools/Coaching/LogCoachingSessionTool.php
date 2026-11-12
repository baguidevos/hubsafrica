<?php

namespace App\Mcp\Tools\Coaching;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Project;
use App\Models\TimelineEvent;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('log_coaching_session')]
#[Title('Consigner un compte-rendu de séance de coaching')]
#[Description('Enregistre une séance de coaching réalisée ou planifiée avec sa thématique, sa durée, son compte-rendu et les prochaines actions convenues.')]
class LogCoachingSessionTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'project_id' => 'required|integer',
            'coach_id' => 'required|integer',
            'theme' => 'required|string|max:200',
            'date' => 'required|date',
            'duration' => 'nullable|string|max:50',
            'hours_delivered' => 'nullable|numeric|min:0.25|max:24',
            'report' => 'nullable|string',
            'objectives' => 'nullable|string',
            'next_actions' => 'nullable|string',
            'status' => 'nullable|string|in:PLANIFIEE,REALISEE,ANNULEE,REPORTEE',
        ]);

        /** @var Project|null $project */
        $project = Project::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['project_id'])
            ->first();

        if (! $project) {
            return Response::error("Projet #{$validated['project_id']} introuvable ou non rattaché à cette structure.");
        }

        /** @var Coach|null $coach */
        $coach = Coach::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['coach_id'])
            ->first();

        if (! $coach) {
            return Response::error("Coach #{$validated['coach_id']} introuvable ou non rattaché à cette structure.");
        }

        $status = $validated['status'] ?? 'REALISEE';
        $hours = $validated['hours_delivered'] ?? 1.5;

        $session = CoachingSession::create([
            'sae_id' => $sae->id,
            'project_id' => $project->id,
            'coach_id' => $coach->id,
            'entrepreneur_id' => $project->entrepreneur_id,
            'cohort_id' => $project->cohorts->first()?->id,
            'theme' => $validated['theme'],
            'date' => $validated['date'],
            'duration' => $validated['duration'] ?? "{$hours}h",
            'hours_delivered' => $hours,
            'report' => $validated['report'] ?? null,
            'objectives' => $validated['objectives'] ?? null,
            'next_actions' => $validated['next_actions'] ?? null,
            'status' => $status,
        ]);

        TimelineEvent::create([
            'sae_id' => $sae->id,
            'project_id' => $project->id,
            'entrepreneur_id' => $project->entrepreneur_id,
            'title' => "Séance de Coaching : {$validated['theme']}",
            'description' => "Séance animée par {$coach->name} ({$session->duration}).".(! empty($validated['report']) ? " Synthèse : {$validated['report']}" : ''),
            'type' => 'COACHING',
            'icon' => 'heroicon-m-academic-cap',
        ]);

        $this->logAudit(
            action: 'LOG_COACHING_SESSION',
            entity: 'CoachingSession',
            entityId: (string) $session->id,
            details: [
                'theme' => $session->theme,
                'coach_id' => $coach->id,
                'project_id' => $project->id,
                'status' => $session->status,
            ]
        );

        return Response::structured([
            'message' => "La séance de coaching « {$session->theme} » a été enregistrée avec succès.",
            'session' => [
                'id' => $session->id,
                'project' => $project->name,
                'coach' => $coach->name,
                'theme' => $session->theme,
                'date' => $session->date?->toIso8601String(),
                'status' => $session->status,
                'hours_delivered' => $session->hours_delivered,
            ],
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Identifiant numérique unique du projet')
                ->required(),
            'coach_id' => $schema->integer()
                ->description('Identifiant numérique unique du coach animateur')
                ->required(),
            'theme' => $schema->string()
                ->description('Thème de la session (ex: Structuration du Business Model, Préparation Pitch, Modèle Financier)')
                ->required(),
            'date' => $schema->string()
                ->description('Date et heure de la séance (format YYYY-MM-DD ou YYYY-MM-DD HH:mm)')
                ->required(),
            'duration' => $schema->string()
                ->description('Durée indicative (ex: "1h30", "2h")')
                ->default('1h30'),
            'hours_delivered' => $schema->number()
                ->description('Volume horaire comptabilisé en heures décimales (ex: 1.5 pour 1h30)')
                ->default(1.5),
            'report' => $schema->string()
                ->description('Compte-rendu détaillé des points abordés et conseils formulés'),
            'objectives' => $schema->string()
                ->description('Objectifs pédagogiques fixés pour la séance'),
            'next_actions' => $schema->string()
                ->description('Plan d\'action ou devoirs à accomplir par l\'entrepreneur avant la prochaine session'),
            'status' => $schema->string()
                ->enum(['PLANIFIEE', 'REALISEE', 'ANNULEE', 'REPORTEE'])
                ->description('Statut de la séance')
                ->default('REALISEE'),
        ];
    }
}
