<?php

namespace App\Support;

use App\Models\Sae;
use App\Models\User;

final class FormSubmissionRedirectResolver
{
    public const SESSION_PROJECT_URL_KEY = 'fb_post_submit_project_url';

    /**
     * Détermine si l'URL fournie correspond déjà au portail entrepreneur (/portal).
     */
    public static function isPortalUrl(?string $url): bool
    {
        if ($url === null || trim($url) === '') {
            return false;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        return $path === 'portal' || str_starts_with($path, 'portal/');
    }

    /**
     * Décision pure de redirection après soumission d'un formulaire de candidature :
     * 1. Projet incomplet (< 100%) -> redirection forcée vers la fiche projet
     * 2. Profil incomplet (< 100%) -> redirection forcée vers le profil entrepreneur
     * 3. Les deux à 100% -> laisse passer vers le portail (ou l'URL configurée)
     *
     * @return array{url: string, label: string, forced: bool, delaySeconds: int}
     */
    public static function resolveForCandidature(
        bool $isProjectComplete,
        bool $isProfileComplete,
        ?string $projectEditUrl,
        ?string $profileUrl,
        string $portalUrl = '/portal',
        int $delaySeconds = 3,
    ): array {
        if (! $isProjectComplete && $projectEditUrl !== null && trim($projectEditUrl) !== '') {
            return [
                'url' => $projectEditUrl,
                'label' => 'Compléter ma fiche projet',
                'forced' => true,
                'delaySeconds' => $delaySeconds,
            ];
        }

        if (! $isProfileComplete && $profileUrl !== null && trim($profileUrl) !== '') {
            return [
                'url' => $profileUrl,
                'label' => 'Compléter mon profil',
                'forced' => true,
                'delaySeconds' => $delaySeconds,
            ];
        }

        return [
            'url' => trim($portalUrl) !== '' ? $portalUrl : '/portal',
            'label' => 'Accéder à mon espace',
            'forced' => false,
            'delaySeconds' => $delaySeconds,
        ];
    }

    /**
     * Fonction de décision pure pour la redirection après soumission de formulaire.
     *
     * @return array{url: string, label: string, delaySeconds: int}|null
     */
    public static function resolve(
        ?string $projectUrl,
        ?string $portalUrl,
        bool $isAlreadyOnPortal,
        int $delaySeconds = 3,
    ): ?array {
        if ($projectUrl !== null && trim($projectUrl) !== '') {
            return [
                'url' => $projectUrl,
                'label' => 'Accéder à mon projet',
                'delaySeconds' => $delaySeconds,
            ];
        }

        if (! $isAlreadyOnPortal && $portalUrl !== null && trim($portalUrl) !== '') {
            return [
                'url' => $portalUrl,
                'label' => 'Accéder à mon espace',
                'delaySeconds' => $delaySeconds,
            ];
        }

        return null;
    }

    /**
     * Résout la redirection post-soumission pour la requête courante (y compris requêtes Livewire).
     *
     * @return array{url: string, label: string, delaySeconds: int}|null
     */
    public static function resolveForCurrentRequest(?User $user, ?Sae $formSae = null): ?array
    {
        $projectUrl = session()->get(self::SESSION_PROJECT_URL_KEY);
        $projectUrl = is_string($projectUrl) && $projectUrl !== '' ? $projectUrl : null;

        $currentPageUrl = request()->header('Referer') ?: request()->url();
        $isAlreadyOnPortal = self::isPortalUrl($currentPageUrl);
        $portalUrl = self::resolvePortalUrlForUser($user, $formSae);

        return self::resolve(
            projectUrl: $projectUrl,
            portalUrl: $portalUrl,
            isAlreadyOnPortal: $isAlreadyOnPortal,
        );
    }

    private static function resolvePortalUrlForUser(?User $user, ?Sae $formSae = null): ?string
    {
        if (! $user) {
            return null;
        }

        if ($formSae && $user->saes()->where('saes.id', $formSae->id)->wherePivot('role', 'ENTREPRENEUR')->exists()) {
            return route('filament.entrepreneur.pages.dashboard', ['tenant' => $formSae->slug]);
        }

        $entrepreneurSae = $user->saes()
            ->wherePivot('role', 'ENTREPRENEUR')
            ->first();

        if ($entrepreneurSae) {
            return route('filament.entrepreneur.pages.dashboard', ['tenant' => $entrepreneurSae->slug]);
        }

        if ($user->isEntrepreneur()) {
            return route('filament.entrepreneur.tenant');
        }

        return null;
    }
}
