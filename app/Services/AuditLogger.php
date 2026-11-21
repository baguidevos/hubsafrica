<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Sae;
use App\Models\User;
use BackedEnum;
use DateTimeInterface;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * @var list<string>
     */
    public const DEFAULT_IGNORED_ATTRIBUTES = [
        'created_at',
        'updated_at',
        'deleted_at',
        'remember_token',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected static bool $enabled = true;

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }

    /**
     * Execute a callback without recording automatic audit logs.
     */
    public static function withoutAuditing(callable $callback): mixed
    {
        $previousState = self::$enabled;
        self::$enabled = false;

        try {
            return $callback();
        } finally {
            self::$enabled = $previousState;
        }
    }

    public static function record(
        AuditAction|string $action,
        string $entity,
        string|int|null $entityId = null,
        ?string $details = null,
        ?int $saeId = null,
        ?User $user = null,
    ): ?AuditLog {
        if (! self::$enabled) {
            return null;
        }

        $actor = $user ?? (auth()->user() instanceof User ? auth()->user() : null);
        $resolvedSaeId = self::resolveSaeId($saeId, $actor);

        return AuditLog::withoutGlobalScopes()->create([
            'sae_id' => $resolvedSaeId,
            'user_id' => $actor?->getKey(),
            'user_name' => $actor?->name ?? 'Système',
            'action' => $action instanceof AuditAction ? $action->value : $action,
            'entity' => $entity,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'details' => $details,
            'ip_address' => request()?->ip(),
        ]);
    }

    public static function resolveEntityName(Model $model): string
    {
        return class_basename($model);
    }

    public static function resolveModelLabel(Model $model): string
    {
        foreach (['name', 'title', 'full_name', 'email', 'slug'] as $attribute) {
            $value = $model->getAttribute($attribute);

            if (is_string($value) && trim($value) !== '') {
                return Str::limit(trim($value), 120);
            }
        }

        return sprintf('%s #%s', self::resolveEntityName($model), (string) $model->getKey());
    }

    public static function resolveModelSaeId(Model $model): ?int
    {
        if ($model instanceof Sae) {
            return $model->getKey() ? (int) $model->getKey() : null;
        }

        $modelSaeId = $model->getAttribute('sae_id');

        if (is_numeric($modelSaeId)) {
            return (int) $modelSaeId;
        }

        return null;
    }

    /**
     * Pure formatter turning Eloquent attribute diffs into a readable audit summary.
     *
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $original
     * @param  list<string>  $ignoredAttributes
     */
    public static function formatModelChanges(
        array $changes,
        array $original,
        array $ignoredAttributes = self::DEFAULT_IGNORED_ATTRIBUTES,
    ): ?string {
        $ignoredLookup = array_flip($ignoredAttributes);
        $formattedDiffs = [];

        foreach ($changes as $attribute => $newValue) {
            if (isset($ignoredLookup[$attribute])) {
                continue;
            }

            $oldFormatted = self::stringifyValue($original[$attribute] ?? null);
            $newFormatted = self::stringifyValue($newValue);

            if ($oldFormatted === $newFormatted) {
                continue;
            }

            $formattedDiffs[] = "{$attribute}: {$oldFormatted} -> {$newFormatted}";
        }

        if ($formattedDiffs === []) {
            return null;
        }

        return implode(' | ', $formattedDiffs);
    }

    protected static function resolveSaeId(?int $explicitSaeId, ?User $actor): ?int
    {
        if ($explicitSaeId !== null) {
            return $explicitSaeId;
        }

        if (Filament::hasTenancy() && $tenant = Filament::getTenant()) {
            return (int) $tenant->getKey();
        }

        if ($actor && is_numeric($actor->active_sae_id)) {
            return (int) $actor->active_sae_id;
        }

        return null;
    }

    protected static function stringifyValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'vide';
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? 'oui' : 'non';
        }

        if (is_array($value)) {
            $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);

            return Str::limit($encoded !== false ? $encoded : '[]', 120);
        }

        return Str::limit((string) $value, 120);
    }
}
