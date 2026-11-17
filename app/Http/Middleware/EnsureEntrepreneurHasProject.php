<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEntrepreneurHasProject
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (! $user || $user->isSuperAdmin()) {
            return $next($request);
        }

        $entrepreneur = $user->entrepreneurProfile;

        if (! $entrepreneur) {
            return $next($request);
        }

        $hasProject = Project::withoutGlobalScopes()
            ->where('entrepreneur_id', $entrepreneur->id)
            ->exists();

        if ($hasProject) {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');

        if (
            $routeName === 'filament.entrepreneur.pages.dashboard'
            || str_starts_with($routeName, 'filament.entrepreneur.resources.projects.')
            || str_starts_with($routeName, 'filament.entrepreneur.auth.')
            || str_starts_with($routeName, 'livewire.')
        ) {
            return $next($request);
        }

        Notification::make()
            ->title('Fiche projet requise')
            ->body('Veuillez d\'abord compléter votre fiche projet depuis le tableau de bord avant de consulter les autres rubriques de votre portail.')
            ->warning()
            ->send();

        $tenant = Filament::getTenant() ?? $entrepreneur->sae;

        return redirect()->route('filament.entrepreneur.pages.dashboard', [
            'tenant' => $tenant,
        ]);
    }
}
