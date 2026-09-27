<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

        return redirect(filament()->getPanel('coach')->getUrl($invitation->sae));
    }
}
