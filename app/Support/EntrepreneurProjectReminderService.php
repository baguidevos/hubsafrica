<?php

namespace App\Support;

use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Mail\CompleteProjectCohortInviteMail;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\Sae;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class EntrepreneurProjectReminderService
{
    /**
     * Entrepreneurs sans projet inscrits via au moins un formulaire de candidature.
     */
    public function candidatesWithoutProjectQuery(?int $saeId): Builder
    {
        return Entrepreneur::query()
            ->when($saeId !== null, fn (Builder $query) => $query->where('sae_id', $saeId))
            ->whereDoesntHave('projects')
            ->whereHas('applications')
            ->with([
                'applications' => fn ($query) => $query->latest('id')->with(['cohort.program', 'program']),
                'user',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    /**
     * Entrepreneurs sans projet inscrits hors formulaire de candidature.
     */
    public function directEntrepreneursWithoutProjectQuery(?int $saeId): Builder
    {
        return Entrepreneur::query()
            ->when($saeId !== null, fn (Builder $query) => $query->where('sae_id', $saeId))
            ->whereDoesntHave('projects')
            ->whereDoesntHave('applications')
            ->with([
                'enrollments' => fn ($query) => $query->latest('id')->with(['cohort.program']),
                'user',
            ])
            ->orderBy('first_name')
            ->orderBy('last_name');
    }

    /**
     * @return array<int, string>
     */
    public function activeCohortOptions(?int $saeId): array
    {
        return Cohort::query()
            ->when($saeId !== null, fn (Builder $query) => $query->where('sae_id', $saeId))
            ->whereIn('status', [CohortStatus::OUVERTE, CohortStatus::EN_COURS])
            ->with('program')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Cohort $cohort): array => [
                $cohort->id => $cohort->name.($cohort->program ? " ({$cohort->program->name})" : ''),
            ])
            ->all();
    }

    public function formatCandidateOptionLabel(Entrepreneur $entrepreneur): string
    {
        $latestApplication = $entrepreneur->applications->first();
        $cohortName = $latestApplication?->cohort?->name;
        $programName = $latestApplication?->program?->name ?? $latestApplication?->cohort?->program?->name;

        $contextParts = array_filter([
            $programName ? "Programme : {$programName}" : null,
            $cohortName ? "Cohorte : {$cohortName}" : null,
        ]);

        $contextSuffix = $contextParts !== []
            ? ' — '.implode(' | ', $contextParts)
            : ' — Candidature sans cohorte';

        $emailPart = filled($entrepreneur->email) ? " ({$entrepreneur->email})" : ' (sans email)';

        return "{$entrepreneur->full_name}{$emailPart}{$contextSuffix}";
    }

    public function formatDirectEntrepreneurOptionLabel(Entrepreneur $entrepreneur): string
    {
        $linkedPrograms = $entrepreneur->enrollments
            ->map(function (Enrollment $enrollment): ?string {
                $cohortName = $enrollment->cohort?->name;
                $programName = $enrollment->cohort?->program?->name;

                if ($programName && $cohortName) {
                    return "{$programName} ({$cohortName})";
                }

                return $programName ?? $cohortName;
            })
            ->filter()
            ->unique()
            ->values();

        $programSuffix = $linkedPrograms->isNotEmpty()
            ? ' — Programme lié : '.$linkedPrograms->implode(', ')
            : ' — Aucun programme lié';

        $statusLabel = $entrepreneur->status?->getLabel() ?? 'Statut inconnu';
        $emailPart = filled($entrepreneur->email) ? " ({$entrepreneur->email})" : ' (sans email)';

        return "{$entrepreneur->full_name}{$emailPart} [{$statusLabel}]{$programSuffix}";
    }

    public function defaultCandidateSubject(): string
    {
        return '[Action requise] Finalisez votre fiche projet pour valider votre candidature sur Hubs Africa';
    }

    public function defaultCandidateMessageHtml(): string
    {
        return <<<'HTML'
<p>Votre candidature a bien été enregistrée sur <strong>Hubs Africa</strong>, mais nous constatons qu'<strong>aucune fiche projet n'est encore complétée</strong> sur votre profil candidat.</p>
<p>Pour que votre dossier puisse être examiné et retenu pour la suite du processus de sélection, <strong>la création et la complétion de votre fiche projet sont obligatoires</strong>.</p>
<ul>
    <li><strong>Validation de candidature :</strong> un dossier sans fiche projet reste incomplet et ne peut pas être évalué par le jury.</li>
    <li><strong>Accès aux étapes suivantes :</strong> l'intégration définitive dans la cohorte et l'accompagnement nécessitent une fiche projet renseignée.</li>
</ul>
<p><strong>Action attendue :</strong> connectez-vous dès maintenant à votre Espace Entrepreneur via le bouton ci-dessous pour renseigner votre projet (nom, secteur, stade de maturité et description).</p>
HTML;
    }

    public function defaultDirectSubject(): string
    {
        return '[Action requise] Finalisez votre fiche projet pour activer votre profil sur Hubs Africa';
    }

    public function defaultDirectMessageHtml(): string
    {
        return <<<'HTML'
<p>Votre compte entrepreneur sur <strong>Hubs Africa</strong> est bien actif, mais nous constatons qu'<strong>aucun projet n'y est encore associé</strong>.</p>
<p>Pour que votre inscription soit pleinement prise en compte et que votre profil reste éligible aux opportunités de la plateforme, <strong>la création et la complétion de votre fiche projet sont obligatoires</strong>.</p>
<ul>
    <li><strong>Éligibilité aux programmes :</strong> impossible de bénéficier pleinement des appels à projets et cohortes des structures d'accompagnement (SAEs).</li>
    <li><strong>Visibilité auprès des partenaires :</strong> votre profil n'apparaît pas auprès des incubateurs, accélérateurs, mentors et investisseurs.</li>
    <li><strong>Accès au suivi :</strong> l'attribution d'un coach et l'accès aux outils de structuration (Data Room, livrables) nécessitent une fiche projet validée.</li>
</ul>
<p><strong>Action attendue :</strong> connectez-vous dès maintenant à votre Espace Entrepreneur via le bouton ci-dessous pour renseigner votre projet dans les plus brefs délais.</p>
HTML;
    }

    /**
     * Invite les candidats issus d'un formulaire de candidature à compléter leur projet,
     * en leur attribuant/conservant le statut CANDIDAT sans créer d'enrollment ni les lier à une cohorte.
     *
     * @param  Collection<int, Entrepreneur>  $entrepreneurs
     * @return array{emailed: int, skipped_no_email: int}
     */
    public function sendCandidateReminders(
        Collection $entrepreneurs,
        ?Sae $tenant,
        ?string $subject,
        ?string $messageHtml,
    ): array {
        $emailedCount = 0;
        $skippedNoEmailCount = 0;

        foreach ($entrepreneurs as $entrepreneur) {
            $sae = $tenant ?? $entrepreneur->sae;
            $saeId = $sae?->id ?? $entrepreneur->sae_id;

            if ($entrepreneur->status !== EntrepreneurStatus::CANDIDAT) {
                $entrepreneur->update(['status' => EntrepreneurStatus::CANDIDAT]);
            }

            $this->ensureUserAttachedToSae($entrepreneur, $saeId);

            if (blank($entrepreneur->email)) {
                $skippedNoEmailCount++;

                continue;
            }

            $latestApplication = $entrepreneur->relationLoaded('applications')
                ? $entrepreneur->applications->first()
                : $entrepreneur->applications()->latest('id')->with('cohort.program')->first();

            Mail::to($entrepreneur->email)->queue(
                new CompleteProjectCohortInviteMail(
                    entrepreneur: $entrepreneur,
                    cohort: $latestApplication?->cohort,
                    sae: $sae,
                    customMessage: $messageHtml,
                    customSubject: $subject,
                    isCohortEnrollment: false,
                )
            );

            $emailedCount++;
        }

        return [
            'emailed' => $emailedCount,
            'skipped_no_email' => $skippedNoEmailCount,
        ];
    }

    /**
     * Invite les entrepreneurs hors candidature à compléter leur projet,
     * avec rattachement optionnel à une cohorte.
     *
     * @param  Collection<int, Entrepreneur>  $entrepreneurs
     * @return array{enrolled: int, emailed: int, skipped_no_email: int}
     */
    public function sendDirectReminders(
        Collection $entrepreneurs,
        ?Sae $tenant,
        ?Cohort $cohort,
        ?string $subject,
        ?string $messageHtml,
    ): array {
        $enrolledCount = 0;
        $emailedCount = 0;
        $skippedNoEmailCount = 0;

        foreach ($entrepreneurs as $entrepreneur) {
            $sae = $tenant ?? $entrepreneur->sae;
            $saeId = $sae?->id ?? $entrepreneur->sae_id;

            if ($cohort !== null) {
                $enrollment = Enrollment::firstOrCreate(
                    [
                        'sae_id' => $saeId,
                        'cohort_id' => $cohort->id,
                        'entrepreneur_id' => $entrepreneur->id,
                    ],
                    [
                        'project_id' => $entrepreneur->projects()->value('id'),
                        'status' => 'ACTIF',
                        'enrolled_at' => now(),
                    ]
                );

                if ($enrollment->wasRecentlyCreated) {
                    $enrolledCount++;
                }

                if ($entrepreneur->status === EntrepreneurStatus::CANDIDAT) {
                    $entrepreneur->update(['status' => EntrepreneurStatus::ACTIVE]);
                }
            }

            $this->ensureUserAttachedToSae($entrepreneur, $saeId);

            if (blank($entrepreneur->email)) {
                $skippedNoEmailCount++;

                continue;
            }

            Mail::to($entrepreneur->email)->queue(
                new CompleteProjectCohortInviteMail(
                    entrepreneur: $entrepreneur,
                    cohort: $cohort,
                    sae: $sae,
                    customMessage: $messageHtml,
                    customSubject: $subject,
                    isCohortEnrollment: $cohort !== null,
                )
            );

            $emailedCount++;
        }

        return [
            'enrolled' => $enrolledCount,
            'emailed' => $emailedCount,
            'skipped_no_email' => $skippedNoEmailCount,
        ];
    }

    private function ensureUserAttachedToSae(Entrepreneur $entrepreneur, ?int $saeId): void
    {
        if (! $entrepreneur->user || ! $saeId) {
            return;
        }

        if ($entrepreneur->user->saes()->where('saes.id', $saeId)->exists()) {
            return;
        }

        $entrepreneur->user->saes()->attach($saeId, [
            'role' => 'ENTREPRENEUR',
            'status' => 'ACTIVE',
        ]);
    }
}
