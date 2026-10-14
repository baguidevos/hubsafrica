<?php

namespace App\Http\Controllers\Public;

use App\Enums\CohortStatus;
use App\Http\Controllers\Controller;
use App\Mail\FounderWelcomeMail;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class CohortPublicController extends Controller
{
    /**
     * Point d'entrée public pour rejoindre une cohorte spécifique.
     * Si l'utilisateur est déjà connecté, il est redirigé directement vers le formulaire de candidature.
     * Sinon, il est invité à se connecter ou à créer son compte avant d'accéder au formulaire.
     */
    public function join(int $id): View|RedirectResponse
    {
        $cohort = Cohort::with(['sae.types', 'program.sae.types', 'program.level'])
            ->findOrFail($id);

        $isClosed = $cohort->status === CohortStatus::CLOTUREE;

        // Si l'utilisateur est déjà connecté et que la cohorte est active, redirection immédiate vers le wizard
        if (Auth::check() && ! $isClosed) {
            return redirect()->route('public.programme.candidater', [
                'id' => $cohort->program_id,
                'cohort' => $cohort->id,
            ]);
        }

        Head::title("Rejoindre la promotion {$cohort->name} — {$cohort->program->name} | Hub Africa")
            ->description("Candidatez directement à la promotion {$cohort->name} du programme {$cohort->program->name} porté par {$cohort->sae?->name}.");

        return view('pages.cohort-join', [
            'cohort' => $cohort,
            'isClosed' => $isClosed,
        ]);
    }

    /**
     * Connexion d'un utilisateur existant depuis la passerelle de cohorte.
     */
    public function login(Request $request, int $id): RedirectResponse
    {
        $cohort = Cohort::findOrFail($id);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Veuillez renseigner votre adresse email.',
            'email.email' => 'Adresse email invalide.',
            'password.required' => 'Veuillez saisir votre mot de passe.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->route('public.programme.candidater', [
                'id' => $cohort->program_id,
                'cohort' => $cohort->id,
            ])->with('status', "Ravi de vous revoir ! Vous pouvez dès à présent compléter votre candidature pour la cohorte {$cohort->name}.");
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => 'Identifiants de connexion invalides. Vérifiez votre email et mot de passe.']);
    }

    /**
     * Inscription d'un nouvel entrepreneur depuis la passerelle de cohorte.
     */
    public function register(Request $request, int $id): RedirectResponse
    {
        $cohort = Cohort::with('program')->findOrFail($id);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.unique' => 'Cette adresse email possède déjà un compte. Veuillez vous connecter avec le formulaire ci-dessus.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
            ]);

            Entrepreneur::create([
                'user_id' => $user->id,
                'sae_id' => $cohort->sae_id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'status' => 'ACTIVE',
            ]);

            // Rattacher l'utilisateur à la SAE avec le rôle ENTREPRENEUR si non présent
            if (! $user->saes()->where('saes.id', $cohort->sae_id)->exists()) {
                $user->saes()->attach($cohort->sae_id, [
                    'role' => 'ENTREPRENEUR',
                    'status' => 'ACTIVE',
                ]);
            }

            DB::commit();

            Mail::to($user->email)->queue(new FounderWelcomeMail($user));

            Auth::login($user);
            $request->session()->regenerate();

            return redirect()->route('public.programme.candidater', [
                'id' => $cohort->program_id,
                'cohort' => $cohort->id,
            ])->with('status', 'Votre compte a été créé avec succès ! Veuillez finaliser votre dossier de candidature ci-dessous.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['general' => 'Une erreur est survenue lors de la création de votre compte : '.$e->getMessage()]);
        }
    }
}
