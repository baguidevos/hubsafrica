<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Sae;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class StructurePublicController extends Controller
{
    /**
     * Annuaire des Structures d'Accompagnement à l'Entrepreneuriat (SAEs).
     */
    public function index(): View
    {
        Head::title('Annuaire des Structures d\'Accompagnement (SAEs) | Hub Africa')
            ->description('Découvrez les incubateurs, accélérateurs et technopoles membres du réseau Hub Africa engagés dans l\'accompagnement des startups.');

        $saes = Sae::withCount(['programs', 'projects', 'coaches'])
            ->latest()
            ->paginate(12);

        return view('pages.structures', [
            'saes' => $saes,
        ]);
    }

    /**
     * Fiche d'une structure d'accompagnement et de ses programmes.
     */
    public function show(int $id): View
    {
        $sae = Sae::with(['programs' => function ($q) {
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
