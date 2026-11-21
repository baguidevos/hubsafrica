<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AuditAction: string implements HasColor, HasLabel
{
    case CREATE = 'CREATE';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case LOGIN = 'LOGIN';
    case LOGOUT = 'LOGOUT';
    case PASSWORD_CHANGE = 'PASSWORD_CHANGE';
    case UPDATE_STATUS = 'UPDATE_STATUS';

    public function getLabel(): string
    {
        return match ($this) {
            self::CREATE => 'Création',
            self::UPDATE => 'Modification',
            self::DELETE => 'Suppression',
            self::LOGIN => 'Connexion',
            self::LOGOUT => 'Déconnexion',
            self::PASSWORD_CHANGE => 'Mot de passe modifié',
            self::UPDATE_STATUS => 'Changement de statut',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::CREATE => 'success',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
            self::LOGIN => 'info',
            self::LOGOUT => 'gray',
            self::PASSWORD_CHANGE => 'primary',
            self::UPDATE_STATUS => 'warning',
        };
    }

    public static function labelFor(?string $action): string
    {
        if (! $action) {
            return '—';
        }

        return self::tryFrom($action)?->getLabel() ?? $action;
    }

    public static function colorFor(?string $action): string
    {
        if (! $action) {
            return 'gray';
        }

        return self::tryFrom($action)?->getColor() ?? 'gray';
    }
}
