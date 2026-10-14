<?php

namespace App\Listeners;

use App\Enums\ApplicationStatus;
use App\Enums\FormType;
use App\Enums\Gender;
use App\Enums\ProjectStatus;
use App\Mail\ApplicationReceivedMail;
use App\Mail\FounderWelcomeMail;
use App\Mail\NewApplicationAlertMail;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Packstub\FormBuilder\Events\SubmissionReceived;

class HandleCandidatureSubmission
{
    /**
     * Traite les soumissions de formulaires de type CANDIDATURE générées
     * par le moteur natif Packstub FormBuilder.
     */
    public function handle(SubmissionReceived $event): void
    {
        $form = $event->form;

        // Ne traiter que les formulaires de candidature
        if ($form->type !== FormType::CANDIDATURE) {
            return;
        }

        $submission = $event->submission;
        $data = (array) ($submission->data ?? []);

        // 1. Résolution du programme et de la cohorte
        $programId = $data['program_id'] ?? $form->program_id;
        $cohortId = $data['cohort_id'] ?? null;

        if (! $programId && ! empty($submission->source_url)) {
            if (preg_match('#/programmes/(\d+)#', $submission->source_url, $matches)) {
                $programId = (int) $matches[1];
            }
        }

        if (! $cohortId && ! empty($submission->source_url)) {
            $parsedQuery = [];
            parse_str(parse_url($submission->source_url, PHP_URL_QUERY) ?? '', $parsedQuery);
            if (! empty($parsedQuery['cohort'])) {
                $cohortId = (int) $parsedQuery['cohort'];
            }
        }

        $program = $programId ? Program::find($programId) : null;
        $cohort = $cohortId
            ? Cohort::find($cohortId)
            : $program?->cohorts()->whereIn('status', ['OUVERTE', 'EN_COURS'])->latest()->first();

        $saeId = $program?->sae_id ?? $form->sae_id;

        DB::beginTransaction();

        try {
            // 2. Résolution ou création du compte utilisateur
            $userId = $event->context->userId ?? $submission->user_id ?? Auth::id();
            $user = $userId ? User::find($userId) : null;

            $email = $data['email'] ?? null;

            if (! $user && $email) {
                $user = User::where('email', $email)->first();

                if (! $user) {
                    $firstName = $data['first_name'] ?? '';
                    $lastName = $data['last_name'] ?? '';
                    $fullName = trim("{$firstName} {$lastName}") ?: 'Candidat Hub Africa';

                    $user = User::create([
                        'name' => $fullName,
                        'email' => $email,
                        'password' => Hash::make(Str::random(16)),
                        'email_verified_at' => now(),
                    ]);
                }
            }

            if (! $user) {
                DB::rollBack();

                return;
            }

            // 3. Création ou mise à jour du profil Entrepreneur
            $entrepreneur = Entrepreneur::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();

            $genderValue = ($data['gender'] ?? 'M') === 'F' ? Gender::F : Gender::M;

            if (! $entrepreneur) {
                $names = explode(' ', $user->name, 2);
                $entrepreneur = Entrepreneur::create([
                    'user_id' => $user->id,
                    'sae_id' => $saeId,
                    'first_name' => $data['first_name'] ?? ($names[0] ?? 'Candidat'),
                    'last_name' => $data['last_name'] ?? ($names[1] ?? ''),
                    'email' => $user->email,
                    'phone' => $data['phone'] ?? '',
                    'gender' => $genderValue,
                    'territory' => $data['city'] ?? $data['territory'] ?? 'Dakar',
                    'status' => 'ACTIVE',
                ]);
            } else {
                $updates = [];
                if (empty($entrepreneur->phone) && ! empty($data['phone'])) {
                    $updates['phone'] = $data['phone'];
                }
                if (empty($entrepreneur->territory) && (! empty($data['city']) || ! empty($data['territory']))) {
                    $updates['territory'] = $data['city'] ?? $data['territory'];
                }
                if ($saeId && empty($entrepreneur->sae_id)) {
                    $updates['sae_id'] = $saeId;
                }
                if (! empty($updates)) {
                    $entrepreneur->update($updates);
                }
            }

            // 4. Création ou association du Projet (Startup)
            $projectName = $data['project_name'] ?? ('Projet '.$entrepreneur->first_name);

            $project = Project::where('entrepreneur_id', $entrepreneur->id)
                ->where('name', $projectName)
                ->first();

            if (! $project) {
                $project = Project::create([
                    'sae_id' => $saeId,
                    'entrepreneur_id' => $entrepreneur->id,
                    'name' => $projectName,
                    'sector' => $data['sector'] ?? 'Général',
                    'pitch' => $data['pitch'] ?? '',
                    'team_size' => (int) ($data['team_size'] ?? 1) ?: 1,
                    'status' => ProjectStatus::ACTIF,
                    'current_level_id' => $program?->level_id,
                    'current_level_entered_at' => now(),
                ]);
            }

            // 5. Traitement des pièces jointes (Pitch Deck)
            $pitchDeck = $data['pitch_deck'] ?? null;
            if (is_array($pitchDeck)) {
                $pitchDeck = reset($pitchDeck);
            }

            // 6. Extraction des réponses spécifiques (hors champs standards)
            $standardKeys = [
                'first_name', 'last_name', 'email', 'phone', 'gender', 'city',
                'project_name', 'sector', 'pitch', 'team_size', 'website',
                'motivation', 'needs', 'pitch_deck', 'program_id', 'cohort_id',
                '_fb_website', '_fb_token',
            ];

            $customAnswers = array_diff_key($data, array_flip($standardKeys));

            $commentBody = "Motivation du candidat :\n".($data['motivation'] ?? 'Non renseignée');
            if (! empty($data['needs'])) {
                $needs = is_array($data['needs']) ? implode(', ', $data['needs']) : $data['needs'];
                $commentBody .= "\n\nBesoins identifiés : ".$needs;
            }
            if ($pitchDeck) {
                $deckUrl = str_starts_with((string) $pitchDeck, 'http') ? $pitchDeck : asset("storage/{$pitchDeck}");
                $commentBody .= "\nPitch deck déposé : ".$deckUrl;
            }
            if (! empty($customAnswers)) {
                $commentBody .= "\n\n--- Questions spécifiques de la structure ---\n";
                foreach ($customAnswers as $key => $val) {
                    $valStr = is_array($val) ? implode(', ', $val) : (string) $val;
                    $commentBody .= "• {$key} : {$valStr}\n";
                }
            }

            // 7. Enregistrement officiel de la candidature
            $application = Application::create([
                'sae_id' => $saeId,
                'program_id' => $program?->id,
                'cohort_id' => $cohort?->id,
                'form_id' => $form->id,
                'answers' => $customAnswers,
                'entrepreneur_id' => $entrepreneur->id,
                'project_id' => $project->id,
                'status' => ApplicationStatus::SOUMISE,
                'submitted_at' => now(),
                'comment' => $commentBody,
            ]);

            // 8. Traçabilité dans la timeline du projet
            TimelineEvent::create([
                'sae_id' => $saeId,
                'project_id' => $project->id,
                'entrepreneur_id' => $entrepreneur->id,
                'title' => 'Candidature officielle déposée : '.($program?->name ?? 'Programme'),
                'description' => 'Le dossier de candidature a été validé et transmis au comité de sélection via le formulaire standardisé/personnalisé.',
                'type' => 'MILESTONE',
                'icon' => 'heroicon-m-document-text',
            ]);

            // 9. Association de l'utilisateur à la soumission
            if (! $submission->user_id) {
                $submission->update(['user_id' => $user->id]);
            }

            DB::commit();

            // 10. Connexion automatique de l'utilisateur si non connecté
            if (! Auth::check()) {
                Auth::login($user);
            }

            // 11. Notification in-app & Email pour l'entrepreneur
            Notification::make()
                ->title('Candidature enregistrée avec succès')
                ->body("Votre dossier de candidature pour le programme {$program?->name} a été officiellement transmis.")
                ->success()
                ->sendToDatabase($user);

            if ($user->wasRecentlyCreated) {
                Mail::to($user->email)
                    ->queue(new FounderWelcomeMail($user, $entrepreneur));
            }

            Mail::to($user->email)
                ->queue(new ApplicationReceivedMail($application));

            // 12. Notification in-app & Email aux responsables de la SAE
            if ($saeId) {
                $saeUsers = User::whereHas('saes', fn ($q) => $q->where('saes.id', $saeId))->get();
                foreach ($saeUsers as $saeUser) {
                    if ($saeUser->id === $user->id) {
                        continue;
                    }
                    Notification::make()
                        ->title('Nouvelle candidature reçue')
                        ->body("{$entrepreneur->full_name} a déposé une candidature pour le programme '{$program?->name}'.")
                        ->icon('heroicon-o-clipboard-document-check')
                        ->info()
                        ->sendToDatabase($saeUser);

                    if ($saeUser->email) {
                        Mail::to($saeUser->email)
                            ->queue(new NewApplicationAlertMail($application));
                    }
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
        }
    }
}
