<?php

namespace App\Http\Controllers\Public;

use App\Enums\CoachJoinRequestStatus;
use App\Http\Controllers\Controller;
use App\Mail\CoachJoinRequestReceivedMail;
use App\Models\CoachJoinRequest;
use App\Models\Sae;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class CoachJoinRequestPublicController extends Controller
{
    /**
     * Affiche le formulaire public pour postuler comme coach auprès d'une SAE.
     */
    public function create(Request $request): View
    {
        Head::title('Devenir Coach / Mentor — Hubs Africa')
            ->description('Postulez pour rejoindre l\'équipe de mentorat d\'une structure d\'accompagnement partenaire de Hubs Africa.');

        $selectedSaeId = $request->query('sae');
        $saes = Sae::where('status', 'ACTIVE')->orderBy('name')->get();

        return view('pages.coach-join-request', [
            'saes' => $saes,
            'selectedSaeId' => $selectedSaeId ? (int) $selectedSaeId : null,
        ]);
    }

    /**
     * Enregistre la demande de rattachement du coach et notifie les responsables de la SAE.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sae_id' => ['required', 'exists:saes,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'bio' => ['required', 'string', 'min:30', 'max:2000'],
        ], [
            'sae_id.required' => 'Veuillez sélectionner la structure d\'accompagnement (SAE) que vous souhaitez rejoindre.',
            'name.required' => 'Votre nom complet est obligatoire.',
            'email.required' => 'Votre adresse email est obligatoire.',
            'bio.required' => 'Veuillez rédiger une courte présentation de vos compétences et motivations.',
            'bio.min' => 'Votre présentation doit comporter au moins 30 caractères.',
        ]);

        $sae = Sae::findOrFail($validated['sae_id']);

        // Convertir specialties en tableau si séparé par des virgules
        $specialtiesArray = ! empty($validated['specialties'])
            ? array_filter(array_map('trim', explode(',', $validated['specialties'])))
            : [];

        $joinRequest = CoachJoinRequest::create([
            'sae_id' => $sae->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'specialties' => $specialtiesArray,
            'linkedin_url' => $validated['linkedin_url'] ?? null,
            'bio' => $validated['bio'],
            'status' => CoachJoinRequestStatus::PENDING,
        ]);

        // Notifications in-app & Email aux responsables de la SAE
        $saeUsers = User::whereHas('saes', fn ($q) => $q->where('saes.id', $sae->id))->get();
        foreach ($saeUsers as $saeUser) {
            Notification::make()
                ->title('Nouvelle candidature de Coach / Mentor')
                ->body("{$joinRequest->name} souhaite intégrer l'équipe de mentorat de {$sae->name}.")
                ->icon('heroicon-o-academic-cap')
                ->info()
                ->sendToDatabase($saeUser);

            if ($saeUser->email) {
                Mail::to($saeUser->email)->queue(new CoachJoinRequestReceivedMail($joinRequest));
            }
        }

        return redirect()->route('public.rejoindre')->with('status_success', "Votre demande pour intégrer l'équipe de {$sae->name} a été transmise avec succès ! Vous recevrez un email dès que le responsable aura validé votre candidature.");
    }
}
