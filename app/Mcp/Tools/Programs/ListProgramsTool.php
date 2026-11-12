<?php

namespace App\Mcp\Tools\Programs;

use App\Mcp\Concerns\InteractsWithSae;
use App\Models\Program;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Title;
use Laravel\Mcp\Server\Tool;

#[Name('list_programs')]
#[Title('Lister les programmes d\'accompagnement')]
#[Description('Récupère les programmes d\'accompagnement de la SAE avec leurs cohortes associées, dates d\'inscription et niveau ciblé.')]
class ListProgramsTool extends Tool
{
    use InteractsWithSae;

    public function handle(Request $request): Response|ResponseFactory
    {
        $sae = $this->currentSae();

        $validated = $request->validate([
            'is_published' => 'nullable|boolean',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Program::query()
            ->where('sae_id', $sae->id)
            ->with(['level', 'cohorts'])
            ->withCount(['cohorts', 'applications'])
            ->latest();

        if (isset($validated['is_published'])) {
            $query->where('is_published', $validated['is_published']);
        }

        $limit = $validated['limit'] ?? 20;
        $programs = $query->take($limit)->get();

        $data = $programs->map(fn (Program $p) => [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'is_published' => (bool) $p->is_published,
            'level' => $p->level?->name,
            'duration' => $p->duration,
            'admission_threshold' => $p->admission_threshold,
            'registration_start' => $p->registration_start?->toDateString(),
            'registration_end' => $p->registration_end?->toDateString(),
            'cohorts_count' => $p->cohorts_count,
            'applications_count' => $p->applications_count,
            'is_accepting_applications' => $p->isAcceptingApplications(),
        ])->all();

        return Response::structured([
            'count' => count($data),
            'sae' => $sae->name,
            'programs' => $data,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'is_published' => $schema->boolean()
                ->description('Filtrer par statut de publication (true pour publiés, false pour brouillons)'),
            'limit' => $schema->integer()
                ->description('Nombre maximal de programmes à renvoyer')
                ->default(20),
        ];
    }
}
