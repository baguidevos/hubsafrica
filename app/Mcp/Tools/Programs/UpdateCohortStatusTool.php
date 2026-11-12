<?php

namespace App\Mcp\Tools\Programs;

use App\Enums\CohortStatus;
use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Cohort;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('update_cohort_status')]
#[Title('Changer le statut d\'une cohorte')]
#[Description('Permet d\'ouvrir aux candidatures, de passer en cours d\'accompagnement ou de clôturer une cohorte.')]
class UpdateCohortStatusTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'cohort_id' => 'required|integer',
            'status' => 'required|string|in:OUVERTE,EN_COURS,CLOTUREE',
        ]);

        /** @var Cohort|null $cohort */
        $cohort = Cohort::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['cohort_id'])
            ->first();

        if (! $cohort) {
            return Response::error("Cohorte #{$validated['cohort_id']} introuvable ou non rattachée à cette SAE.");
        }

        $oldStatus = $cohort->status?->value ?? (string) $cohort->status;
        $newStatus = CohortStatus::from($validated['status']);
        $cohort->status = $newStatus;
        $cohort->save();

        $this->logAudit(
            action: 'UPDATE_STATUS',
            entity: 'Cohort',
            entityId: (string) $cohort->id,
            details: [
                'old_status' => $oldStatus,
                'new_status' => $newStatus->value,
            ]
        );

        return Response::text("Le statut de la cohorte « {$cohort->name} » a été mis à jour avec succès : {$oldStatus} → {$newStatus->value}.");
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'cohort_id' => $schema->integer()
                ->description('Identifiant numérique unique de la cohorte')
                ->required(),
            'status' => $schema->string()
                ->enum(['OUVERTE', 'EN_COURS', 'CLOTUREE'])
                ->description('Nouveau statut de la cohorte (OUVERTE pour accepter des candidatures, EN_COURS pour l\'accompagnement, CLOTUREE pour finaliser)')
                ->required(),
        ];
    }
}
