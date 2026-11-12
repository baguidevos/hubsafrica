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

#[Name('update_application_status')]
#[Title('Modifier le statut d\'une candidature')]
#[Description('Met à jour manuellement le statut d\'un dossier de candidature (ex: SOUMISE, EN_REVUE, ACCEPTEE, REFUSEE, EN_ATTENTE) avec motif.')]
class UpdateApplicationStatusTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'application_id' => 'required|integer',
            'status' => 'required|string|in:BROUILLON,SOUMISE,EN_REVUE,ACCEPTEE,REFUSEE,EN_ATTENTE',
            'decision_note' => 'nullable|string|max:500',
        ]);

        /** @var Application|null $application */
        $application = Application::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['application_id'])
            ->first();

        if (! $application) {
            return Response::error("Candidature #{$validated['application_id']} introuvable ou non rattachée à cette structure.");
        }

        $oldStatus = $application->status?->value ?? (string) $application->status;
        $newStatusEnum = ApplicationStatus::tryFrom($validated['status']) ?? ApplicationStatus::from($validated['status']);

        $application->status = $newStatusEnum;
        $application->reviewed_at = now();
        if (! empty($validated['decision_note'])) {
            $application->decision = $validated['decision_note'];
        }
        $application->save();

        if ($newStatusEnum === ApplicationStatus::ACCEPTEE && method_exists($application, 'enrollInCohort')) {
            $application->enrollInCohort();
        }

        $this->logAudit(
            action: 'UPDATE_STATUS',
            entity: 'Application',
            entityId: (string) $application->id,
            details: [
                'old_status' => $oldStatus,
                'new_status' => $newStatusEnum->value,
                'decision_note' => $validated['decision_note'] ?? null,
            ]
        );

        return Response::text("Le statut de la candidature #{$application->id} ({$application->code}) a été mis à jour avec succès : {$oldStatus} → {$newStatusEnum->value}.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'application_id' => $schema->integer()
                ->description('Identifiant numérique unique de la candidature')
                ->required(),
            'status' => $schema->string()
                ->enum(['BROUILLON', 'SOUMISE', 'EN_REVUE', 'ACCEPTEE', 'REFUSEE', 'EN_ATTENTE'])
                ->description('Nouveau statut à attribuer')
                ->required(),
            'decision_note' => $schema->string()
                ->description('Commentaire ou motif officiel de la décision'),
        ];
    }
}
