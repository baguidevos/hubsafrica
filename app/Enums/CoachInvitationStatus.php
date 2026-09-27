<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CoachInvitationStatus: string implements HasLabel, HasColor
{
    case PENDING = 'PENDING';
    case ACCEPTED = 'ACCEPTED';
    case EXPIRED = 'EXPIRED';
    case REVOKED = 'REVOKED';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::ACCEPTED => 'Acceptée',
            self::EXPIRED => 'Expirée',
            self::REVOKED => 'Révoquée',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::ACCEPTED => 'success',
            self::EXPIRED => 'gray',
            self::REVOKED => 'danger',
        };
    }
}
