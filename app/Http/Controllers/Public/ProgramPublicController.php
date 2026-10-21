<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Project;
use App\Services\ApplicationFormTemplateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class ProgramPublicController extends Controller
{
    /**
     * Catalogue public des programmes d'accompagnement.
     */
    public function index(): View
    {
        Head::title('Programmes d\'Accompagnement & Appels à Candidatures | Hubs Africa')
            ->description('Découvrez tous les programmes d\'incubation et d\'accélération actifs en Afrique. Filtrez par incubateur, niveau et postulez en ligne.');

        return view('pages.programmes');
    }

    /**
     * Fiche institutionnelle détaillée d'un programme d'accompagnement.
     */
    public function show(int $id): View
    {
        $program = Program::with(['sae.types', 'level', 'cohorts', 'deliverableDefinitions'])
            ->findOrFail($id);

        Head::title("{$program->name} — Programme d'Accompagnement | Hubs Africa")
            ->description(strip_tags($program->description ?: 'Consultez les détails, critères d\'éligibilité et calendrier de ce programme d\'accompagnement.'))
            ->og(
                type: 'article',
                title: $program->name,
                description: strip_tags($program->description ?: 'Appel à candidatures Hubs Africa'),
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

        $cohortId = request()->query('cohort');
        $cohort = $cohortId
            ? ($program->cohorts()->find($cohortId) ?? Cohort::find($cohortId))
            : $program->cohorts()->whereIn('status', ['OUVERTE', 'EN_COURS'])->latest()->first();

        $templateService = app(ApplicationFormTemplateService::class);
        $applicationForm = $cohort
            ? $templateService->getEffectiveFormForCohort($cohort)
            : $templateService->getOrCreateStandardTemplate();

        $prefillValues = [];
        if (Auth::check()) {
            $user = Auth::user();
            $entrepreneur = $user->entrepreneurProfile;

            if ($entrepreneur) {
                $prefillValues['first_name'] = $entrepreneur->first_name ?? '';
                $prefillValues['last_name'] = $entrepreneur->last_name ?? '';
                $prefillValues['email'] = $entrepreneur->email ?? $user->email;
                $prefillValues['phone'] = $entrepreneur->phone ?? '';
                $prefillValues['gender'] = is_object($entrepreneur->gender) ? $entrepreneur->gender->value : ($entrepreneur->gender ?? 'M');
                $prefillValues['city'] = $entrepreneur->city ?? '';

                $existingProject = Project::where('entrepreneur_id', $entrepreneur->id)->first();
                if ($existingProject) {
                    $prefillValues['project_name'] = $existingProject->name;
                    $prefillValues['sector'] = $existingProject->sector ?? '';
                    $prefillValues['pitch'] = $existingProject->pitch ?? '';
                    $prefillValues['team_size'] = $existingProject->team_size ?? 1;
                    $prefillValues['website'] = $existingProject->website ?? '';
                }
            } else {
                $prefillValues['email'] = $user->email;
                $names = explode(' ', $user->name, 2);
                $prefillValues['first_name'] = $names[0] ?? '';
                $prefillValues['last_name'] = $names[1] ?? '';
            }
        }

        $prefillValues['program_id'] = (string) $program->id;
        if ($cohort) {
            $prefillValues['cohort_id'] = (string) $cohort->id;
        }

        Head::title("Candidater au programme : {$program->name} | Hubs Africa")
            ->description("Formulaire officiel de candidature en ligne pour intégrer le programme {$program->name}.");

        return view('pages.programme-apply', [
            'program' => $program,
            'cohort' => $cohort,
            'applicationForm' => $applicationForm,
            'prefillValues' => $prefillValues,
        ]);
    }
}
