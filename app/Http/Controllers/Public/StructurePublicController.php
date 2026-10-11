<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sae;
use App\Models\SaeType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class StructurePublicController extends Controller
{
    /**
     * Annuaire des Structures d'Accompagnement à l'Entrepreneuriat (SAEs).
     */
    public function index(Request $request): View
    {
        Head::title('Annuaire des Structures d\'Accompagnement (SAEs) | Hub Africa')
            ->description('Découvrez les incubateurs, accélérateurs, espaces de coworking et technopoles membres du réseau Hub Africa engagés dans l\'accompagnement des startups.');

        $selectedType = $request->query('type');

        $query = Sae::with(['types'])
            ->withCount(['programs', 'projects', 'coaches'])
            ->latest();

        if ($selectedType) {
            $query->whereHas('types', function ($q) use ($selectedType) {
                $q->where('slug', $selectedType);
            });
        }

        $saes = $query->paginate(12)->withQueryString();

        $types = SaeType::where('is_active', true)
            ->withCount('saes')
            ->orderBy('name')
            ->get();

        return view('pages.structures', [
            'saes' => $saes,
            'types' => $types,
            'selectedType' => $selectedType,
        ]);
    }

    /**
     * Fiche d'une structure d'accompagnement et de ses programmes.
     */
    public function show(int $id): View
    {
        $sae = Sae::with(['types', 'programs' => function ($q) {
            $q->where('status', 'ACTIF')->latest();
        }, 'coaches' => function ($q) {
            $q->where('status', 'ACTIF');
        }])
            ->withCount(['projects', 'coaches'])
            ->findOrFail($id);

        Head::title("{$sae->name} — Structure d'Accompagnement | Hub Africa")
            ->description(strip_tags($sae->description ?: "Découvrez les programmes et mentors de la structure {$sae->name}."));

        return view('pages.structure-detail', [
            'sae' => $sae,
        ]);
    }
}
