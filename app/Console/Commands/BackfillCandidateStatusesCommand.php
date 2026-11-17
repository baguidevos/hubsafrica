<?php

namespace App\Console\Commands;

use App\Enums\ApplicationStatus;
use App\Enums\CoachAssignmentStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\ProjectStatus;
use App\Models\Entrepreneur;
use App\Models\Project;
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
    protected $description = 'Corrige les statuts des projets et entrepreneurs issus de candidatures non acceptées (passage en CANDIDAT) et garantit leur accès portail.';

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

        $missingPortalEntrepreneurs = Entrepreneur::withoutGlobalScopes()
            ->whereNotNull('user_id')
            ->whereNotNull('sae_id')
            ->with('user')
            ->get()
            ->filter(function (Entrepreneur $entrepreneur): bool {
                $user = $entrepreneur->user;

                return $user !== null && ! $user->saes()->where('saes.id', $entrepreneur->sae_id)->exists();
            });

        $this->table(
            ['Correction', 'Enregistrements concernés'],
            [
                ['Projets ACTIF → CANDIDAT', $candidateProjectIds->count()],
                ['Entrepreneurs ACTIVE → CANDIDAT', $candidateEntrepreneurIds->count()],
                ['Accès portail SAE à rattacher', $missingPortalEntrepreneurs->count()],
            ]
        );

        if ($isDryRun) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($candidateProjectIds, $candidateEntrepreneurIds, $missingPortalEntrepreneurs): void {
            if ($candidateProjectIds->isNotEmpty()) {
                Project::withoutGlobalScopes()
                    ->whereIn('id', $candidateProjectIds)
                    ->update(['status' => ProjectStatus::CANDIDAT->value]);
            }

            if ($candidateEntrepreneurIds->isNotEmpty()) {
                Entrepreneur::withoutGlobalScopes()
                    ->whereIn('id', $candidateEntrepreneurIds)
                    ->update(['status' => EntrepreneurStatus::CANDIDAT->value]);
            }

            foreach ($missingPortalEntrepreneurs as $entrepreneur) {
                $entrepreneur->user->saes()->attach($entrepreneur->sae_id, [
                    'role' => 'ENTREPRENEUR',
                    'status' => 'ACTIVE',
                ]);
            }
        });

        $this->info('Correction des données terminée avec succès.');

        return self::SUCCESS;
    }
}
