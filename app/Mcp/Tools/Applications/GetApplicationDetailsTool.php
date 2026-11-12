<?php

namespace App\Mcp\Tools\Applications;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('get_application_details')]
#[Title('Consulter une candidature en détail')]
#[Description('Récupère l\'intégralité du dossier d\'une candidature : informations personnelles, réponses au formulaire, projet et évaluations.')]
class GetApplicationDetailsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'application_id' => 'required|integer',
        ]);

        /** @var Application|null $application */
        $application = Application::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['application_id'])
            ->with([
                'program',
                'cohort',
                'project.currentLevel',
                'entrepreneur.user',
                'evaluations.evaluator',
            ])
            ->first();

        if (! $application) {
            return Response::error("Candidature #{$validated['application_id']} introuvable ou non rattachée à cette structure.");
        }

        $evaluations = $application->evaluations->map(fn ($eval) => [
            'id' => $eval->id,
            'evaluator_name' => $eval->evaluator?->name ?? 'Jury',
            'score' => $eval->total_score ?? $eval->score,
            'status' => $eval->status?->value ?? (string) $eval->status,
            'comments' => $eval->comment,
        ])->all();

        return Response::structured([
            'id' => $application->id,
            'code' => $application->code,
            'status' => $application->status?->value ?? (string) $application->status,
            'score' => $application->score,
            'decision' => $application->decision,
            'submitted_at' => $application->submitted_at?->toIso8601String(),
            'reviewed_at' => $application->reviewed_at?->toIso8601String(),
            'candidate' => [
                'name' => $application->entrepreneur?->user?->name,
                'email' => $application->entrepreneur?->user?->email,
                'phone' => $application->entrepreneur?->phone,
                'gender' => $application->entrepreneur?->gender,
                'city' => $application->entrepreneur?->city,
                'country' => $application->entrepreneur?->country,
            ],
            'project' => [
                'id' => $application->project?->id,
                'name' => $application->project?->name,
                'sector' => $application->project?->sector,
                'description' => $application->project?->description,
                'problem' => $application->project?->problem,
                'solution' => $application->project?->solution,
                'target' => $application->project?->target,
                'team_size' => $application->project?->team_size,
                'current_level' => $application->project?->currentLevel?->name,
            ],
            'program' => [
                'id' => $application->program?->id,
                'name' => $application->program?->name,
            ],
            'cohort' => [
                'id' => $application->cohort?->id,
                'name' => $application->cohort?->name,
            ],
            'form_responses' => $application->form_responses ?? [],
            'evaluations' => $evaluations,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'application_id' => $schema->integer()
                ->description('Identifiant numérique unique de la candidature')
                ->required(),
        ];
    }
}
