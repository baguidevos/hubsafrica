<?php

namespace App\Observers;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditModelObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof AuditLog || ! AuditLogger::isEnabled()) {
            return;
        }

        AuditLogger::record(
            action: AuditAction::CREATE,
            entity: AuditLogger::resolveEntityName($model),
            entityId: $model->getKey(),
            details: 'Création : '.AuditLogger::resolveModelLabel($model),
            saeId: AuditLogger::resolveModelSaeId($model),
        );
    }

    public function updated(Model $model): void
    {
        if ($model instanceof AuditLog || ! AuditLogger::isEnabled()) {
            return;
        }

        $diffSummary = AuditLogger::formatModelChanges(
            changes: $model->getChanges(),
            original: $model->getOriginal(),
        );

        if ($diffSummary === null) {
            return;
        }

        AuditLogger::record(
            action: AuditAction::UPDATE,
            entity: AuditLogger::resolveEntityName($model),
            entityId: $model->getKey(),
            details: $diffSummary,
            saeId: AuditLogger::resolveModelSaeId($model),
        );
    }

    public function deleted(Model $model): void
    {
        if ($model instanceof AuditLog || ! AuditLogger::isEnabled()) {
            return;
        }

        AuditLogger::record(
            action: AuditAction::DELETE,
            entity: AuditLogger::resolveEntityName($model),
            entityId: $model->getKey(),
            details: 'Suppression : '.AuditLogger::resolveModelLabel($model),
            saeId: AuditLogger::resolveModelSaeId($model),
        );
    }
}
