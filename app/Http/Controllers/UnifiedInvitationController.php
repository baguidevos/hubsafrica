<?php

namespace App\Http\Controllers;

use App\Models\CoachInvitation;
use App\Models\EntrepreneurInvitation;
use App\Models\Sae;
use Illuminate\Http\Request;

class UnifiedInvitationController extends Controller
{
    public function __construct(
        protected CoachInvitationController $coachController,
        protected EntrepreneurInvitationController $entrepreneurController,
    ) {}

    public function show(string $token)
    {
        $coachInvitation = CoachInvitation::where('token', $token)->first();
        if ($coachInvitation) {
            return $this->coachController->show($token);
        }

        $saeGenericCoach = Sae::where('coach_invite_token', $token)->first();
        if ($saeGenericCoach) {
            return $this->coachController->showGeneric($saeGenericCoach);
        }

        $entrepreneurInvitation = EntrepreneurInvitation::where('token', $token)->first();
        if ($entrepreneurInvitation) {
            return $this->entrepreneurController->show($token);
        }

        return view('auth.entrepreneur-invite-invalid', [
            'message' => 'Cette invitation est invalide, a expiré ou n\'existe pas.',
        ]);
    }

    public function register(Request $request, string $token)
    {
        $coachInvitation = CoachInvitation::where('token', $token)->first();
        if ($coachInvitation) {
            return $this->coachController->register($request, $token);
        }

        $saeGenericCoach = Sae::where('coach_invite_token', $token)->first();
        if ($saeGenericCoach) {
            return $this->coachController->registerGeneric($request, $saeGenericCoach);
        }

        $entrepreneurInvitation = EntrepreneurInvitation::where('token', $token)->first();
        if ($entrepreneurInvitation) {
            return $this->entrepreneurController->register($request, $token);
        }

        return redirect()->back()->withErrors([
            'token' => 'Cette invitation a expiré ou n\'est plus valide.',
        ]);
    }
}
