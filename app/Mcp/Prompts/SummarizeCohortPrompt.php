<?php

namespace App\Mcp\Prompts;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Cohort;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('summarize_cohort')]
#[Title('Rédiger le bilan d\'une promotion')]
#[Description('Génère un prompt pour rédiger un rapport d\'étape ou un bilan final d\'une promotion destiné aux partenaires institutionnels et bailleurs.')]
class SummarizeCohortPrompt extends Prompt
{
    use InteractsWithSae;

    public function arguments(): array
    {
        return [
            new Argument(
                name: 'cohort_id',
                description: 'Identifiant numérique de la cohorte',
                required: true,
            ),
        ];
    }

    public function handle(Request $request): array
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'cohort_id' => 'required|integer',
        ]);

        /** @var Cohort|null $cohort */
        $cohort = Cohort::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['cohort_id'])
            ->with(['program', 'projects.currentLevel'])
            ->first();

        if (! $cohort) {
            return [
                Response::text("Cohorte #{$validated['cohort_id']} introuvable."),
            ];
        }

        $projectNames = $cohort->projects->pluck('name')->implode(', ');

        $instruction = <<<EOT
Tu es le Responsable des Opérations de l'incubateur « {$sae->name} ».
Rédige un bilan d'étape exécutif et professionnel pour la cohorte « {$cohort->name} » du programme « {$cohort->program?->name} ».

Données de la promotion :
- Période : du {$cohort->start_date?->toDateString()} au {$cohort->end_date?->toDateString()}
- Statut : {$cohort->status?->value}
- Startups accompagnées ({$cohort->projects->count()}) : {$projectNames}

Structure attendue du rapport :
1. Résumé exécutif des faits marquants
2. Évolution de la maturité des startups (franchissement des jalons)
3. Activités de mentorat et ateliers réalisés
4. Difficultés rencontrées et mesures correctives
5. Prochaines étapes et livrables attendus pour la fin de programme.
EOT;

        return [
            Response::text($instruction)->asAssistant(),
        ];
    }
}
