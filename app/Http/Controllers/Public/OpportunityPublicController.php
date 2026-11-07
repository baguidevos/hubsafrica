<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class OpportunityPublicController extends Controller
{
    /**
     * Catalogue public des opportunités et appels à projets.
     */
    public function index(): View
    {
        Head::title('Opportunités & Appels à Projets | Hubs Africa')
            ->description('Découvrez les appels à projets, financements, concours et opportunités d\'affaires publiés par les structures d\'accompagnement en Afrique.');

        return view('pages.opportunities');
    }

    /**
     * Fiche publique détaillée d'une opportunité.
     */
    public function show(string $slug): View
    {
        $opportunity = Opportunity::withoutGlobalScopes()
            ->with('sae')
            ->where('slug', $slug)
            ->firstOrFail();

        $plainDescription = strip_tags($opportunity->details);
        $shortDescription = mb_substr($plainDescription, 0, 160) ?: 'Consultez les détails, critères d\'éligibilité et postulez en ligne.';

        Head::title("{$opportunity->title} — Opportunité | Hubs Africa")
            ->description($shortDescription)
            ->og(
                type: 'article',
                title: $opportunity->title,
                description: $shortDescription,
            );

        return view('pages.opportunity-detail', [
            'opportunity' => $opportunity,
        ]);
    }
}
