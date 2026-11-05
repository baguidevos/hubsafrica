<?php

namespace App\Http\Controllers;

use App\Mail\CoachWelcomeMail;
use App\Models\Coach;
use App\Models\CoachInvitation;
use App\Models\Sae;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;

class CoachInvitationController extends Controller
{
    public function show(string $token)
    {
        $invitation = CoachInvitation::where('token', $token)->first();

        if (! $invitation || ! $invitation->isPending()) {
            return view('auth.coach-invite-invalid', [
                'message' => 'Cette invitation est invalide, a expiré ou a déjà été utilisée.',
            ]);
        }

        return view('auth.coach-invite', [
            'invitation' => $invitation,
            'sae' => $invitation->sae,
        ]);
    }

    public function register(Request $request, string $token)
    {
        $invitation = CoachInvitation::where('token', $token)->firstOrFail();

        if (! $invitation->isPending()) {
            return redirect()->back()->withErrors(['token' => 'Cette invitation a expiré ou n\'est plus valide.']);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($invitation, $validated) {
            // Création ou récupération de l'utilisateur
            $user = User::updateOrCreate(
                ['email' => $invitation->email],
                [
                    'name' => $validated['name'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'status' => 'ACTIVE',
                ]
            );

            // Rattachement à la SAE
            $user->saes()->syncWithoutDetaching([
                $invitation->sae_id => [
                    'role' => 'COACH',
                    'status' => 'ACTIVE',
                ],
            ]);

            // Attribution du rôle Coach dans le tenant
            setPermissionsTeamId($invitation->sae_id);
            $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
            $user->assignRole($coachRole);

            // Création / mise à jour du profil Coach
            Coach::updateOrCreate(
                [
                    'sae_id' => $invitation->sae_id,
                    'user_id' => $user->id,
                ],
                [
                    'name' => $validated['name'],
                    'email' => $invitation->email,
                    'phone' => $validated['phone'] ?? null,
                    'specialties' => $invitation->specialties,
                    'max_capacity' => $invitation->max_capacity,
                    'is_external' => $invitation->is_external,
                    'status' => 'ACTIVE',
                ]
            );

            // Clôture de l'invitation
            $invitation->update([
                'status' => 'ACCEPTED',
                'accepted_at' => now(),
            ]);

            return $user;
        });

        Auth::login($user);

        $coach = Coach::where('user_id', $user->id)->first();
        Mail::to($user->email)->queue(new CoachWelcomeMail($user, $coach, $invitation->sae));

        return redirect(filament()->getPanel('coach')->getUrl($invitation->sae));
    }

    public function showGeneric(Sae $sae)
    {
        return view('auth.coach-invite', [
            'invitation' => null,
            'sae' => $sae,
            'isGeneric' => true,
            'token' => $sae->coach_invite_token,
        ]);
    }

    public function registerGeneric(Request $request, Sae $sae)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialties' => ['nullable', 'string', 'max:255'],
        ], [
            'email.required' => 'Votre adresse email est obligatoire.',
            'name.required' => 'Votre nom complet est obligatoire.',
            'password.required' => 'Veuillez définir un mot de passe sécurisé.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $specialtiesArray = ! empty($validated['specialties'])
            ? array_filter(array_map('trim', explode(',', $validated['specialties'])))
            : null;

        $user = DB::transaction(function () use ($sae, $validated, $specialtiesArray) {
            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name' => $validated['name'],
                    'password' => Hash::make($validated['password']),
                    'phone' => $validated['phone'] ?? null,
                    'status' => 'ACTIVE',
                ]
            );

            $user->saes()->syncWithoutDetaching([
                $sae->id => [
                    'role' => 'COACH',
                    'status' => 'ACTIVE',
                ],
            ]);

            setPermissionsTeamId($sae->id);
            $coachRole = Role::firstOrCreate(['name' => 'coach', 'guard_name' => 'web']);
            $user->assignRole($coachRole);

            Coach::updateOrCreate(
                [
                    'sae_id' => $sae->id,
                    'user_id' => $user->id,
                ],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'specialties' => $specialtiesArray,
                    'status' => 'ACTIVE',
                ]
            );

            return $user;
        });

        Auth::login($user);

        $coach = Coach::where('user_id', $user->id)->first();
        Mail::to($user->email)->queue(new CoachWelcomeMail($user, $coach, $sae));

        return redirect(filament()->getPanel('coach')->getUrl($sae));
    }
}
