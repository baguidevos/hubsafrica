<?php

namespace App\Http\Controllers;

use App\Enums\EntrepreneurInvitationStatus;
use App\Mail\FounderWelcomeMail;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use App\Models\EntrepreneurInvitation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class EntrepreneurInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = EntrepreneurInvitation::with(['sae', 'cohort.program'])->where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            return view('auth.entrepreneur-invite-invalid', [
                'message' => 'Cette invitation est invalide, a expiré ou a déjà été utilisée.',
            ]);
        }

        return view('auth.entrepreneur-invite', [
            'invitation' => $invitation,
            'sae' => $invitation->sae,
            'cohort' => $invitation->cohort,
        ]);
    }

    public function register(Request $request, string $token)
    {
        $invitation = EntrepreneurInvitation::with('sae')->where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->back()->withErrors(['token' => 'Cette invitation a expiré ou n\'est plus valide.']);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'project_sector' => ['nullable', 'string', 'max:100'],
            'project_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = DB::transaction(function () use ($invitation, $validated) {
            // Création ou mise à jour de l'utilisateur
            $user = User::where('email', $invitation->email)->first();

            if (! $user) {
                $user = User::create([
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'email' => $invitation->email,
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'status' => 'ACTIVE',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? $user->phone,
                    'status' => 'ACTIVE',
                ]);
            }

            // Rattachement à la SAE
            $user->saes()->syncWithoutDetaching([
                $invitation->sae_id => [
                    'role' => 'ENTREPRENEUR',
                    'status' => 'ACTIVE',
                ],
            ]);

            // Attribution du rôle entrepreneur dans le tenant
            setPermissionsTeamId($invitation->sae_id);
            $entrepreneurRole = Role::firstOrCreate(['name' => 'entrepreneur', 'guard_name' => 'web']);
            $user->assignRole($entrepreneurRole);

            // Création / mise à jour du profil Entrepreneur
            $entrepreneur = Entrepreneur::updateOrCreate(
                [
                    'sae_id' => $invitation->sae_id,
                    'user_id' => $user->id,
                ],
                [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $invitation->email,
                    'phone' => $validated['phone'] ?? null,
                    'status' => 'ACTIVE',
                ]
            );

            // Création du projet initial
            $projectName = ! empty($validated['project_name'])
                ? $validated['project_name']
                : ($invitation->project_name ?: 'Mon Projet Startup');

            $project = Project::firstOrCreate(
                [
                    'sae_id' => $invitation->sae_id,
                    'entrepreneur_id' => $entrepreneur->id,
                    'name' => $projectName,
                ],
                [
                    'description' => $validated['project_description'] ?? null,
                    'sector' => $validated['project_sector'] ?? null,
                    'status' => 'ACTIF',
                ]
            );

            // Si rattaché à une cohorte, inscription immédiate dans la cohorte
            if ($invitation->cohort_id) {
                Enrollment::firstOrCreate(
                    [
                        'sae_id' => $invitation->sae_id,
                        'cohort_id' => $invitation->cohort_id,
                        'entrepreneur_id' => $entrepreneur->id,
                    ],
                    [
                        'project_id' => $project->id,
                        'status' => 'ACTIF',
                        'enrolled_at' => now(),
                    ]
                );
            }

            // Clôture de l'invitation
            $invitation->update([
                'status' => EntrepreneurInvitationStatus::ACCEPTED,
                'accepted_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        $entrepreneur = Entrepreneur::where('user_id', $user->id)->first();
        Mail::to($user->email)->queue(new FounderWelcomeMail($user, $entrepreneur));

        $redirectUrl = filament()->getPanel('entrepreneur')->getUrl($invitation->sae);

        return redirect($redirectUrl)->with('status', 'Votre compte a été activé avec succès ! Bienvenue sur Hub Africa.');
    }
}
