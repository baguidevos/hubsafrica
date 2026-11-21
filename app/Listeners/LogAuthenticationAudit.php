<?php

namespace App\Listeners;

use App\Enums\AuditAction;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

class LogAuthenticationAudit
{
    public function handle(Login|Logout|PasswordReset $event): void
    {
        $user = $event->user instanceof User ? $event->user : null;

        if (! $user) {
            return;
        }

        [$action, $details] = match (true) {
            $event instanceof Login => [AuditAction::LOGIN, "Connexion de {$user->email}"],
            $event instanceof Logout => [AuditAction::LOGOUT, "Déconnexion de {$user->email}"],
            $event instanceof PasswordReset => [AuditAction::PASSWORD_CHANGE, "Réinitialisation du mot de passe de {$user->email}"],
        };

        AuditLogger::record(
            action: $action,
            entity: 'User',
            entityId: $user->getKey(),
            details: $details,
            user: $user,
        );
    }
}
