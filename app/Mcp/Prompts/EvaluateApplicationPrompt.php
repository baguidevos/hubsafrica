<?php

namespace App\Mcp\Prompts;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Application;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Name('evaluate_application')]
#[Title('Grille d\'évaluation d\'une candidature')]
#[Description('Prépare un modèle d\'analyse structuré pour évaluer une candidature sur 5 critères clés (Innovation, Équipe, Marché, Faisabilité, Impact).')]
class EvaluateApplicationPrompt extends Prompt
{
    use InteractsWithSae;

    public function arguments(): array
    {
        return [
            new Argument(
                name: 'application_id',
                description: 'Identifiant numérique de la candidature à évaluer',
                required: true,
            ),
        ];
    }

    public function handle(Request $request): array
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'application_id' => 'required|integer',
        ]);

        /** @var Application|null $application */
        $application = Application::query()
            ->where('sae_id', $sae->id)
            ->where('id', $validated['application_id'])
            ->with(['project', 'entrepreneur.user', 'program', 'cohort'])
            ->first();

        if (! $application) {
            return [
                Response::text("Candidature #{$validated['application_id']} introuvable."),
            ];
        }

        $project = $application->project;
        $candidate = $application->entrepreneur?->user;

        $promptInstruction = <<<EOT
Tu es l'expert du comité de sélection de la structure « {$sae->name} ».
Tu dois analyser la candidature suivante pour le programme « {$application->program?->name} » (Promotion : {$application->cohort?->name}).

Dossier Candidat :
- Code : {$application->code}
- Porteur : {$candidate?->name} ({$candidate?->email})
- Projet : {$project?->name}
- Secteur : {$project?->sector}
- Problème identifié : {$project?->problem}
- Solution proposée : {$project?->solution}
- Proposition de valeur : {$project?->value_proposition}
- Cible / Marché : {$project?->target}
- Taille de l'équipe : {$project?->team_size}

Grille d'évaluation demandée :
1. Pertinence du problème et caractère innovant de la solution (/25)
2. Compétences et complémentarité de l'équipe (/25)
3. Modèle économique et potentiel de marché (/25)
4. Faisabilité opérationnelle et adéquation avec le programme (/25)

Fournis :
- Une analyse synthétique des points forts et des risques identifiés.
- Une note globale recommandée sur 100.
- Un avis final justifié : ADMISSION (≥ {$application->getAdmissionThreshold()}/100), LISTE D'ATTENTE ou REFUS.
EOT;

        return [
            Response::text($promptInstruction)->asAssistant(),
        ];
    }
}
