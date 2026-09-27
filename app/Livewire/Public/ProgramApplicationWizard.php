<?php

namespace App\Livewire\Public;

use App\Enums\ApplicationStatus;
use App\Enums\Gender;
use App\Enums\ProjectStatus;
use App\Models\Application;
use App\Models\Entrepreneur;
use App\Models\Program;
use App\Models\Project;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProgramApplicationWizard extends Component
{
    use WithFileUploads;

    public Program $program;

    // Étape active (1: Profil, 2: Projet, 3: Dossier/Motivation, 4: Confirmation)
    public int $currentStep = 1;
    public bool $isSubmitted = false;

    // Étape 1 : Profil Entrepreneur
    #[Validate('required|string|max:100')]
    public string $first_name = '';

    #[Validate('required|string|max:100')]
    public string $last_name = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('required|string|max:30')]
    public string $phone = '';

    #[Validate('required|in:M,F')]
    public string $gender = 'M';

    public string $city = '';

    public string $password = '';
    public string $password_confirmation = '';

    // Étape 2 : Projet & Startup
    #[Validate('required|string|max:150')]
    public string $project_name = '';

    #[Validate('required|string|max:100')]
    public string $sector = '';

    #[Validate('required|string|max:2000')]
    public string $pitch = '';

    public ?int $team_size = 1;
    public string $website = '';

    // Étape 3 : Motivation & Pièces Jointes
    #[Validate('required|string|max:2500')]
    public string $motivation = '';

    public array $needs = [];

    public $pitch_deck = null;

    public function mount(Program $program): void
    {
        $this->program = $program;

        // Si l'utilisateur est déjà connecté en tant qu'entrepreneur, pré-remplir ses données
        if (Auth::check()) {
            $user = Auth::user();
            $entrepreneur = $user->entrepreneurProfile;

            if ($entrepreneur) {
                $this->first_name = $entrepreneur->first_name ?? '';
                $this->last_name = $entrepreneur->last_name ?? '';
                $this->email = $entrepreneur->email ?? $user->email;
                $this->phone = $entrepreneur->phone ?? '';
                $this->gender = is_object($entrepreneur->gender) ? $entrepreneur->gender->value : ($entrepreneur->gender ?? 'M');
                $this->city = $entrepreneur->city ?? '';

                $existingProject = Project::where('entrepreneur_id', $entrepreneur->id)->first();
                if ($existingProject) {
                    $this->project_name = $existingProject->name;
                    $this->sector = $existingProject->sector ?? '';
                    $this->pitch = $existingProject->pitch ?? '';
                    $this->team_size = $existingProject->team_size ?? 1;
                }
            } else {
                $this->email = $user->email;
                $names = explode(' ', $user->name, 2);
                $this->first_name = $names[0] ?? '';
                $this->last_name = $names[1] ?? '';
            }
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
            return;
        }

        if ($this->currentStep === 1) {
            $rules = [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:30',
                'gender' => 'required|in:M,F',
            ];

            if (! Auth::check()) {
                $rules['password'] = 'required|min:8|same:password_confirmation';
            }

            $this->validate($rules);
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'project_name' => 'required|string|max:150',
                'sector' => 'required|string|max:100',
                'pitch' => 'required|string|max:2000',
            ]);
        }

        $this->currentStep = $step;
    }

    public function submitApplication(): void
    {
        $this->validate([
            'motivation' => 'required|string|max:2500',
            'pitch_deck' => 'nullable|file|mimes:pdf,pptx,ppt|max:15360', // max 15 Mo
        ]);

        DB::beginTransaction();

        try {
            $user = Auth::user();

            // 1. Création ou Récupération du compte utilisateur
            if (! $user) {
                $user = User::where('email', $this->email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => trim("{$this->first_name} {$this->last_name}"),
                        'email' => $this->email,
                        'password' => Hash::make($this->password),
                        'email_verified_at' => now(),
                    ]);
                }
            }

            // 2. Création ou mise à jour du profil Entrepreneur
            $entrepreneur = Entrepreneur::where('user_id', $user->id)
                ->orWhere('email', $this->email)
                ->first();

            if (! $entrepreneur) {
                $entrepreneur = Entrepreneur::create([
                    'user_id' => $user->id,
                    'sae_id' => $this->program->sae_id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'gender' => $this->gender === 'F' ? Gender::F : Gender::M,
                    'city' => $this->city ?: 'Dakar',
                    'status' => 'ACTIVE',
                ]);
            }

            // 3. Création ou association du Projet
            $project = Project::where('entrepreneur_id', $entrepreneur->id)
                ->where('name', $this->project_name)
                ->first();

            if (! $project) {
                $project = Project::create([
                    'sae_id' => $this->program->sae_id,
                    'entrepreneur_id' => $entrepreneur->id,
                    'name' => $this->project_name,
                    'sector' => $this->sector,
                    'pitch' => $this->pitch,
                    'team_size' => $this->team_size ?: 1,
                    'status' => ProjectStatus::ACTIF,
                    'current_level_id' => $this->program->level_id,
                    'current_level_entered_at' => now(),
                ]);
            }

            // 4. Téléversement du pitch deck si fourni
            $deckPath = null;
            if ($this->pitch_deck) {
                $deckPath = $this->pitch_deck->store('candidatures/pitch-decks', 'public');
            }

            // 5. Recherche d'une cohorte active ou ouverte pour le programme
            $activeCohort = $this->program->cohorts()
                ->whereIn('status', ['OUVERTE', 'EN_COURS'])
                ->latest()
                ->first();

            // 6. Création de la candidature officielle
            $commentBody = "Motivation du candidat :\n" . $this->motivation;
            if (! empty($this->needs)) {
                $commentBody .= "\n\nBesoins identifiés : " . implode(', ', $this->needs);
            }
            if ($deckPath) {
                $commentBody .= "\nPitch deck déposé : " . asset("storage/{$deckPath}");
            }

            $application = Application::create([
                'sae_id' => $this->program->sae_id,
                'program_id' => $this->program->id,
                'cohort_id' => $activeCohort?->id,
                'entrepreneur_id' => $entrepreneur->id,
                'project_id' => $project->id,
                'status' => ApplicationStatus::SOUMISE,
                'submitted_at' => now(),
                'comment' => $commentBody,
            ]);

            // 7. Journalisation de l'événement dans la timeline du projet
            TimelineEvent::create([
                'sae_id' => $this->program->sae_id,
                'project_id' => $project->id,
                'entrepreneur_id' => $entrepreneur->id,
                'title' => "Candidature officielle déposée : {$this->program->name}",
                'description' => "Le dossier de candidature a été validé et transmis au comité de sélection de la structure {$this->program->sae?->name}.",
                'type' => 'MILESTONE',
                'icon' => 'heroicon-m-document-text',
            ]);

            DB::commit();

            // Connecter automatiquement l'utilisateur s'il ne l'était pas
            if (! Auth::check()) {
                Auth::login($user);
            }

            $this->isSubmitted = true;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->addError('submit_error', 'Une erreur est survenue lors de l\'enregistrement de votre dossier : ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.public.program-application-wizard');
    }
}
