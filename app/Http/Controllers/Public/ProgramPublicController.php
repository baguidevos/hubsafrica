<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class ProgramPublicController extends Controller
{
    /**
     * Catalogue public des programmes d'accompagnement.
     */
    public function index(): View
    {
        Head::title('Programmes d\'Accompagnement & Appels à Candidatures | Hub Africa')
            ->description('Découvrez tous les programmes d\'incubation et d\'accélération actifs en Afrique. Filtrez par incubateur, niveau et postulez en ligne.');

        return view('pages.programmes');
    }

    /**
     * Fiche institutionnelle détaillée d'un programme d'accompagnement.
     */
    public function show(int $id): View
    {
        $program = Program::with(['sae', 'level', 'cohorts', 'deliverableDefinitions'])
            ->findOrFail($id);

        Head::title("{$program->name} — Programme d'Accompagnement | Hub Africa")
            ->description(strip_tags($program->description ?: 'Consultez les détails, critères d\'éligibilité et calendrier de ce programme d\'accompagnement.'))
            ->og(
                type: 'article',
                title: $program->name,
                description: strip_tags($program->description ?: 'Appel à candidatures Hub Africa'),
            );

        return view('pages.programme-detail', [
            'program' => $program,
        ]);
    }

    /**
     * Parcours officiel de candidature à un programme.
     */
    public function candidater(int $id): View
    {
        $program = Program::with(['sae', 'level', 'cohorts'])
            ->findOrFail($id);

        Head::title("Candidater au programme : {$program->name} | Hub Africa")
            ->description("Formulaire officiel de candidature en ligne pour intégrer le programme {$program->name}.");

        return view('pages.programme-apply', [
            'program' => $program,
        ]);
    }
}
