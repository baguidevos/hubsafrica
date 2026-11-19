<?php

namespace App\Support;

use App\Models\Entrepreneur;
use App\Models\Project;
use App\Models\Sae;
use App\Models\User;
use BackedEnum;
use Illuminate\Support\Facades\Cache;

final class EntrepreneurCompletionChecker
{
    /**
     * Champs texte/sélection éditables sur le profil entrepreneur.
     *
     * @var array<string, string>
     */
    public const PROFILE_FIELDS = [
        'first_name' => 'Prénom',
        'last_name' => 'Nom',
        'email' => 'Email',
        'phone' => 'Téléphone',
        'gender' => 'Genre',
        'birth_date' => 'Date de naissance',
        'country' => 'Pays',
        'territory' => 'Ville / Territoire',
        'education_level' => 'Niveau d\'études',
        'profession' => 'Profession',
        'professional_status' => 'Statut professionnel',
    ];

    /**
     * Champs texte/sélection éditables sur la fiche projet (hors logo).
     *
     * @var array<string, string>
     */
    public const PROJECT_FIELDS = [
        'name' => 'Nom du projet',
        'sector' => 'Secteur',
        'territory' => 'Territoire',
        'team_size' => 'Taille de l\'équipe',
        'target' => 'Cible',
        'market' => 'Marché',
        'value_proposition' => 'Proposition de valeur',
        'problem' => 'Problème',
        'solution' => 'Solution',
        'product_service' => 'Produit / Service',
        'pitch' => 'Pitch',
        'description' => 'Description',
    ];

    /**
     * Évalue de façon pure la complétion du profil et du projet (aucun accès base/cache).
     *
     * @param  array<string, mixed>|null  $profileValues
     * @param  array<string, mixed>|null  $projectValues
     * @return array{
     *     is_complete: bool,
     *     percentage: int,
     *     profile_complete: bool,
     *     project_complete: bool,
     *     has_project: bool,
     *     missing_profile_fields: array<int, string>,
     *     missing_project_fields: array<int, string>
     * }
     */
    public static function evaluate(?array $profileValues, ?array $projectValues): array
    {
        $missingProfileFields = self::findMissingFields(self::PROFILE_FIELDS, $profileValues ?? []);
        $hasProject = $projectValues !== null;
        $missingProjectFields = $hasProject
            ? self::findMissingFields(self::PROJECT_FIELDS, $projectValues)
            : array_values(self::PROJECT_FIELDS);

        $totalFields = count(self::PROFILE_FIELDS) + count(self::PROJECT_FIELDS);
        $missingCount = count($missingProfileFields) + count($missingProjectFields);
        $filledCount = max(0, $totalFields - $missingCount);
        $percentage = $totalFields > 0 ? (int) round(($filledCount / $totalFields) * 100) : 100;

        $profileComplete = $missingProfileFields === [];
        $projectComplete = $hasProject && $missingProjectFields === [];

        return [
            'is_complete' => $profileComplete && $projectComplete,
            'percentage' => $percentage,
            'profile_complete' => $profileComplete,
            'project_complete' => $projectComplete,
            'has_project' => $hasProject,
            'missing_profile_fields' => $missingProfileFields,
            'missing_project_fields' => $missingProjectFields,
        ];
    }

    public static function cacheKey(int $userId, int $saeId): string
    {
        return "entrepreneur_completion:{$userId}:{$saeId}";
    }

    /**
     * Retourne l'état de complétion mis en cache pour un utilisateur dans une SAE donnée.
     *
     * @return array{
     *     is_complete: bool,
     *     percentage: int,
     *     profile_complete: bool,
     *     project_complete: bool,
     *     has_project: bool,
     *     project_id: int|null,
     *     missing_profile_fields: array<int, string>,
     *     missing_project_fields: array<int, string>
     * }|null
     */
    public static function forUserInSae(?User $user, ?Sae $sae): ?array
    {
        if (! $user || ! $sae) {
            return null;
        }

        return Cache::remember(
            self::cacheKey($user->id, $sae->id),
            now()->addHours(6),
            function () use ($user, $sae): ?array {
                $entrepreneur = Entrepreneur::withoutGlobalScopes()
                    ->where('user_id', $user->id)
                    ->first();

                if (! $entrepreneur) {
                    return null;
                }

                $project = Project::withoutGlobalScopes()
                    ->where('entrepreneur_id', $entrepreneur->id)
                    ->where('sae_id', $sae->id)
                    ->latest('id')
                    ->first();

                $profileValues = self::extractAttributes($entrepreneur, array_keys(self::PROFILE_FIELDS));
                $projectValues = $project
                    ? self::extractAttributes($project, array_keys(self::PROJECT_FIELDS))
                    : null;

                return array_merge(
                    self::evaluate($profileValues, $projectValues),
                    ['project_id' => $project?->id],
                );
            },
        );
    }

    public static function forgetForEntrepreneur(Entrepreneur $entrepreneur): void
    {
        if (! $entrepreneur->user_id) {
            return;
        }

        $saeIds = collect([$entrepreneur->sae_id])
            ->merge(
                Project::withoutGlobalScopes()
                    ->where('entrepreneur_id', $entrepreneur->id)
                    ->pluck('sae_id')
            )
            ->filter()
            ->unique();

        foreach ($saeIds as $saeId) {
            Cache::forget(self::cacheKey((int) $entrepreneur->user_id, (int) $saeId));
        }
    }

    public static function forgetForProject(Project $project): void
    {
        if (! $project->entrepreneur_id || ! $project->sae_id) {
            return;
        }

        $userId = Entrepreneur::withoutGlobalScopes()
            ->whereKey($project->entrepreneur_id)
            ->value('user_id');

        if (! $userId) {
            return;
        }

        Cache::forget(self::cacheKey((int) $userId, (int) $project->sae_id));
    }

    /**
     * @param  array<string, string>  $fieldLabels
     * @param  array<string, mixed>  $values
     * @return array<int, string>
     */
    private static function findMissingFields(array $fieldLabels, array $values): array
    {
        $missing = [];

        foreach ($fieldLabels as $field => $label) {
            if (! self::isFilledValue($values[$field] ?? null, $field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    private static function isFilledValue(mixed $value, string $field): bool
    {
        if ($value instanceof BackedEnum) {
            return trim((string) $value->value) !== '';
        }

        if ($value === null) {
            return false;
        }

        if ($field === 'team_size') {
            return is_numeric($value) && (int) $value > 0;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        return true;
    }

    /**
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    private static function extractAttributes(Entrepreneur|Project $model, array $keys): array
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $model->getAttribute($key);
        }

        return $values;
    }
}
