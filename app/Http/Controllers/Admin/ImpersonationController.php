<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entrepreneur;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Permet au Super Admin de se connecter en tant qu'entrepreneur et d'accéder directement à son portail.
     */
    public function impersonateEntrepreneur(Request $request, Entrepreneur $entrepreneur): RedirectResponse
    {
        $currentUser = Auth::user();

        if (! $currentUser || ! method_exists($currentUser, 'isSuperAdmin') || ! $currentUser->isSuperAdmin()) {
            abort(403, 'Action réservée aux Super Administrateurs Hubs Africa.');
        }

        $targetUser = $entrepreneur->user;

        if (! $targetUser) {
            return back()->with('error', 'Cet entrepreneur ne possède pas encore de compte utilisateur actif.');
        }

        Auth::login($targetUser, remember: true);
        $request->session()->regenerate();

        // Sauvegarder l'ID de l'administrateur dans la session pour permettre le retour
        $request->session()->put('impersonator_admin_id', $currentUser->id);

        $saeSlug = $entrepreneur->sae?->slug;

        if ($saeSlug) {
            return redirect("/portal/{$saeSlug}");
        }

        return redirect('/portal');
    }

    /**
     * Permet à l'administrateur de quitter le portail entrepreneur et de revenir au panel d'administration Hubs-HQ.
     */
    public function stopImpersonating(Request $request): RedirectResponse
    {
        $adminId = session('impersonator_admin_id');

        if (! $adminId) {
            return redirect('/hubs-hq');
        }

        $adminUser = User::find($adminId);
        session()->forget('impersonator_admin_id');

        if ($adminUser) {
            Auth::login($adminUser, remember: true);
            $request->session()->regenerate();
        }

        return redirect('/hubs-hq/entrepreneurs');
    }
}
