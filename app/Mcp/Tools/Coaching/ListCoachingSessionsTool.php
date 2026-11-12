<?php

namespace App\Mcp\Tools\Coaching;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\CoachingSession;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_coaching_sessions')]
#[Title('Lister les séances de coaching')]
#[Description('Consulte les séances de coaching passées ou programmées de la SAE par projet, coach ou statut.')]
class ListCoachingSessionsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'project_id' => 'nullable|integer',
            'coach_id' => 'nullable|integer',
            'status' => 'nullable|string|in:PLANIFIEE,REALISEE,ANNULEE,REPORTEE,TOUS',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = CoachingSession::query()
            ->where('sae_id', $sae->id)
            ->with(['coach.user', 'project:id,name', 'cohort:id,name'])
            ->latest('date');

        if (! empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }

        if (! empty($validated['coach_id'])) {
            $query->where('coach_id', $validated['coach_id']);
        }

        if (! empty($validated['status']) && $validated['status'] !== 'TOUS') {
            $query->where('status', $validated['status']);
        }

        $limit = $validated['limit'] ?? 20;
        $sessions = $query->take($limit)->get();

        $data = $sessions->map(fn (CoachingSession $s) => [
            'id' => $s->id,
            'project_name' => $s->project?->name ?? 'N/A',
            'coach_name' => $s->coach?->name ?? $s->coach?->user?->name ?? 'N/A',
            'date' => $s->date?->toIso8601String(),
            'duration' => $s->duration,
            'hours_delivered' => $s->hours_delivered,
            'theme' => $s->theme,
            'status' => $s->status?->value ?? (string) $s->status,
            'report' => $s->report,
            'next_actions' => $s->next_actions,
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'coaching_sessions' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'project_id' => $schema->integer()
                ->description('Filtrer par identifiant du projet accompagné'),
            'coach_id' => $schema->integer()
                ->description('Filtrer par identifiant du coach'),
            'status' => $schema->string()
                ->enum(['PLANIFIEE', 'REALISEE', 'ANNULEE', 'REPORTEE', 'TOUS'])
                ->description('Filtrer par statut de la séance')
                ->default('TOUS'),
            'limit' => $schema->integer()
                ->description('Nombre maximal de séances à renvoyer')
                ->default(20),
        ];
    }
}
