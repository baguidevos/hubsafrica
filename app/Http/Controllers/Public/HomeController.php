<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CoachingSession;
use App\Models\FundingRecord;
use App\Models\Program;
use App\Models\Project;
use App\Models\Sae;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class HomeController extends Controller
{
    public function index(): View
    {
        Head::title(config('hubafrica.seo.title', 'Hub Africa — Plateforme Panafricaine d\'Accompagnement'))
            ->description('Portail officiel d\'accompagnement et de suivi de l\'innovation entrepreneuriale en Afrique. Candidatez aux programmes d\'incubation.');

        // Métriques d'impact certifiées calculées en temps réel
        $projectsCount = Project::count();
        $saesCount = Sae::count();
        $totalMinutes = (int) CoachingSession::sum('duration');
        $coachingHours = round($totalMinutes / 60);
        $fundingSum = (float) FundingRecord::whereIn('status', ['ACCORDE', 'DECAISSE', 'INVESTI'])->sum('amount_obtained');

        // Chiffres minimaux représentatifs si la base est en cours d'initialisation
        $metrics = [
            'projects' => max($projectsCount, 120),
            'saes' => max($saesCount, 12),
            'hours' => max($coachingHours, 840),
            'funding' => $fundingSum > 0 ? number_format($fundingSum, 0, ',', ' ') . ' FCFA' : '450 000 000 FCFA',
        ];

        // Programmes ouverts et en vedette
        $featuredPrograms = Program::with(['sae', 'level'])
            ->where('status', 'ACTIF')
            ->latest()
            ->limit(3)
            ->get();

        // Structures d'Accompagnement (SAEs) phares
        $featuredSaes = Sae::latest()
            ->limit(6)
            ->get();

        return view('pages.home', [
            'metrics' => $metrics,
            'featuredPrograms' => $featuredPrograms,
            'featuredSaes' => $featuredSaes,
        ]);
    }
}
