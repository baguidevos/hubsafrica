<?php

namespace App\Mcp\Tools\Applications;

use App\Enums\ApplicationStatus;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('score_application')]
#[Title('Noter et évaluer une candidature')]
#[Description('Attribue une note sur 100 à une candidature, applique la décision (acceptée, liste d\'attente ou refusée) selon les seuils SAE et enrôle le projet si admis.')]
class ScoreApplicationTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'application_id' => 'required|integer',
            'score' => 'required|integer|min:0|max:100',
            'status' => 'nullable|string|in:ACCEPTEE,REFUSEE,EN_ATTENTE',
            'jury_comment' => 'nullable|string|max:1000',
        ]);

        /** @var Application|null $application */
        $application = Application::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['application_id'])
            ->first();

        if (! $application) {
            return Response::error("Candidature #{$validated['application_id']} introuvable ou non rattachée à cette structure.");
        }

        $forcedStatus = ! empty($validated['status'])
            ? ApplicationStatus::from($validated['status'])
            : null;

        $application->applyEvaluationDecision(
            score: (int) $validated['score'],
            status: $forcedStatus,
            juryComment: $validated['jury_comment'] ?? null
        );

        $this->logAudit(
            action: 'EVALUATE',
            entity: 'Application',
            entityId: (string) $application->id,
            details: [
                'score' => $validated['score'],
                'resulting_status' => $application->status?->value,
                'jury_comment' => $validated['jury_comment'] ?? null,
            ]
        );

        return Response::text("Évaluation enregistrée pour la candidature #{$application->id} ({$application->code}) : Note {$validated['score']}/100. Décision retenue : {$application->status?->value}. {$application->decision}");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'application_id' => $schema->integer()
                ->description('Identifiant numérique unique de la candidature')
                ->required(),
            'score' => $schema->integer()
                ->description('Note globale sur 100 attribuée au dossier')
                ->required(),
            'status' => $schema->string()
                ->enum(['ACCEPTEE', 'REFUSEE', 'EN_ATTENTE'])
                ->description('Optionnel : forcer un statut précis au lieu du calcul automatique basé sur les seuils d\'admission'),
            'jury_comment' => $schema->string()
                ->description('Appréciation ou synthèse d\'évaluation rédigée par l\'agent ou le jury'),
        ];
    }
}
