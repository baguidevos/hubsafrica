<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\FormType;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Document;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\Program;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCandidateStatusesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sae:fix-candidate-stats {--dry-run : Simuler les corrections sans modifier la base de données}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige les statuts candidats, répare les candidatures sans projet, type les formulaires de candidature et garantit l\'accès portail.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode simulation (--dry-run) activé : aucune modification ne sera enregistrée.');
        }

        $candidateProjectIds = Project::withoutGlobalScopes()
            ->where('status', ProjectStatus::ACTIF->value)
            ->whereHas('applications', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('applications', fn ($q) => $q->withoutGlobalScopes()->where('status', ApplicationStatus::ACCEPTEE->value))
            ->whereDoesntHave('enrollments', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('assignments', fn ($q) => $q->withoutGlobalScopes()->where('status', CoachAssignmentStatus::ACTIF->value))
            ->pluck('id');

        $candidateEntrepreneurIds = Entrepreneur::withoutGlobalScopes()
            ->where('status', EntrepreneurStatus::ACTIVE->value)
            ->whereHas('applications', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('applications', fn ($q) => $q->withoutGlobalScopes()->where('status', ApplicationStatus::ACCEPTEE->value))
            ->whereDoesntHave('enrollments', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('projects', function ($q) use ($candidateProjectIds) {
                $q->withoutGlobalScopes()
                    ->where('status', '!=', ProjectStatus::CANDIDAT->value)
                    ->whereNotIn('id', $candidateProjectIds);
            })
            ->pluck('id');

        // Entrepreneurs ACTIVE créés avant le correctif d'inscription cohorte (aucun projet, aucune candidature, aucun enrollment)
        $orphanActiveEntrepreneurIds = Entrepreneur::withoutGlobalScopes()
            ->where('status', EntrepreneurStatus::ACTIVE->value)
            ->whereDoesntHave('projects', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('applications', fn ($q) => $q->withoutGlobalScopes())
            ->whereDoesntHave('enrollments', fn ($q) => $q->withoutGlobalScopes())
            ->pluck('id');

        // Candidatures dont le projet a été supprimé (project_id = NULL)
        $orphanApplications = Application::withoutGlobalScopes()
            ->whereNull('project_id')
            ->whereNotNull('entrepreneur_id')
            ->with(['entrepreneur', 'program'])
            ->get();

        // Formulaires de candidature sans type défini (ex: BIORYVA)
        $untypedCandidatureFormIds = Form::withoutGlobalScopes()
            ->whereNull('type')
            ->where(function ($q): void {
                $q->whereRaw('LOWER(name) LIKE ?', ['%candidature%'])
                    ->orWhereHas('cohorts', fn ($cq) => $cq->withoutGlobalScopes());
            })
            ->pluck('id');

        $missingPortalEntrepreneurs = Entrepreneur::withoutGlobalScopes()
            ->whereNotNull('user_id')
            ->whereNotNull('sae_id')
            ->with('user')
            ->get()
            ->filter(function (Entrepreneur $entrepreneur): bool {
                $user = $entrepreneur->user;

                return $user instanceof User
                    && ! $user->saes()->where('saes.id', $entrepreneur->sae_id)->exists();
            });

        $this->table(
            ['Correction', 'Enregistrements concernés'],
            [
                ['Projets ACTIF → CANDIDAT (candidature non acceptée)', $candidateProjectIds->count()],
                ['Entrepreneurs ACTIVE → CANDIDAT (candidature non acceptée)', $candidateEntrepreneurIds->count()],
                ['Entrepreneurs ACTIVE → CANDIDAT (inscrits sans projet ni candidature)', $orphanActiveEntrepreneurIds->count()],
                ['Candidatures sans projet à réparer (reconstruct/rattachement)', $orphanApplications->count()],
                ['Formulaires de candidature sans type → CANDIDATURE', $untypedCandidatureFormIds->count()],
                ['Accès portail SAE à rattacher', $missingPortalEntrepreneurs->count()],
            ]
        );

        if ($isDryRun) {
            return self::SUCCESS;
        }

        DB::transaction(function () use (
            $candidateProjectIds,
            $candidateEntrepreneurIds,
            $orphanActiveEntrepreneurIds,
            $orphanApplications,
            $untypedCandidatureFormIds,
            $missingPortalEntrepreneurs
        ): void {
            if ($candidateProjectIds->isNotEmpty()) {
                Project::withoutGlobalScopes()
                    ->whereIn('id', $candidateProjectIds)
                    ->update(['status' => ProjectStatus::CANDIDAT->value]);
            }

            $allEntrepreneurIdsToCandidate = $candidateEntrepreneurIds
                ->merge($orphanActiveEntrepreneurIds)
                ->unique()
                ->values();

            if ($allEntrepreneurIdsToCandidate->isNotEmpty()) {
                Entrepreneur::withoutGlobalScopes()
                    ->whereIn('id', $allEntrepreneurIdsToCandidate)
                    ->update(['status' => EntrepreneurStatus::CANDIDAT->value]);
            }

            if ($untypedCandidatureFormIds->isNotEmpty()) {
                Form::withoutGlobalScopes()
                    ->whereIn('id', $untypedCandidatureFormIds)
                    ->update(['type' => FormType::CANDIDATURE->value]);
            }

            foreach ($orphanApplications as $application) {
                $this->repairOrphanApplication($application);
            }

            foreach ($missingPortalEntrepreneurs as $entrepreneur) {
                $user = $entrepreneur->user;
                if ($user instanceof User && $entrepreneur->sae_id > 0) {
                    $user->saes()->attach($entrepreneur->sae_id, [
                        'role' => 'ENTREPRENEUR',
                        'status' => 'ACTIVE',
                    ]);
                }
            }
        });

        $this->info('Correction des données terminée avec succès.');

        return self::SUCCESS;
    }

    /**
     * Rattache un projet existant ou reconstruit le projet manquant pour une candidature orpheline.
     */
    private function repairOrphanApplication(Application $application): void
    {
        $entrepreneur = $application->entrepreneur;

        if (! $entrepreneur instanceof Entrepreneur) {
            return;
        }

        $project = Project::withoutGlobalScopes()
            ->where('entrepreneur_id', $entrepreneur->id)
            ->when($application->sae_id, fn ($q, $saeId) => $q->where('sae_id', $saeId))
            ->first();

        if (! $project) {
            $submissionData = $this->findSubmissionDataForApplication($application, $entrepreneur);
            $projectName = $this->resolveProjectName($application, $entrepreneur, $submissionData);
            $isAccepted = $application->status === ApplicationStatus::ACCEPTEE->value
                || $application->getAttribute('status') === ApplicationStatus::ACCEPTEE;
            $status = $isAccepted ? ProjectStatus::ACTIF : ProjectStatus::CANDIDAT;
            $program = $application->program;

            $project = Project::create([
                'sae_id' => $application->sae_id ?: $entrepreneur->sae_id,
                'entrepreneur_id' => $entrepreneur->id,
                'name' => $projectName,
                'sector' => (string) ($submissionData['sector'] ?? 'Général'),
                'pitch' => (string) ($submissionData['pitch'] ?? ''),
                'team_size' => max(1, (int) ($submissionData['team_size'] ?? 1)),
                'status' => $status,
                'current_level_id' => $program instanceof Program ? $program->level_id : null,
                'current_level_entered_at' => $application->submitted_at ?? $application->created_at ?? now(),
            ]);
        }

        $application->update(['project_id' => $project->id]);

        TimelineEvent::withoutGlobalScopes()
            ->where('entrepreneur_id', $entrepreneur->id)
            ->whereNull('project_id')
            ->update(['project_id' => $project->id]);
    }

    /**
     * Recherche la soumission FormBuilder correspondante pour retrouver le nom/secteur/pitch du projet.
     *
     * @return array<string, mixed>
     */
    private function findSubmissionDataForApplication(Application $application, Entrepreneur $entrepreneur): array
    {
        if (! $entrepreneur->user_id) {
            return [];
        }

        $rawData = DB::table('form_builder_submissions')
            ->where('user_id', $entrepreneur->user_id)
            ->when($application->form_id, fn ($q, $formId) => $q->where('form_id', $formId))
            ->orderByDesc('id')
            ->value('data');

        if (! is_string($rawData) || $rawData === '') {
            return [];
        }

        $decoded = json_decode($rawData, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Détermine le meilleur nom de projet disponible (soumission > document pitch deck > fallback).
     *
     * @param  array<string, mixed>  $submissionData
     */
    private function resolveProjectName(Application $application, Entrepreneur $entrepreneur, array $submissionData): string
    {
        $fromSubmission = trim((string) ($submissionData['project_name'] ?? ''));
        if ($fromSubmission !== '') {
            return $fromSubmission;
        }

        $pitchDeckDoc = Document::withoutGlobalScopes()
            ->where('documentable_type', Application::class)
            ->where('documentable_id', $application->id)
            ->where('title', 'like', 'Pitch Deck — %')
            ->first();

        if ($pitchDeckDoc && is_string($pitchDeckDoc->title)) {
            $extracted = trim(str_replace('Pitch Deck — ', '', $pitchDeckDoc->title));
            if ($extracted !== '' && $extracted !== 'Projet') {
                return $extracted;
            }
        }

        return 'Projet '.trim((string) ($entrepreneur->first_name ?: 'Candidat'));
    }
}
