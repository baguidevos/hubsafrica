<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccompanimentToolAccessLevel: string implements HasColor, HasLabel
{
    case FREE = 'FREE';
    case ACCOUNT_REQUIRED = 'ACCOUNT_REQUIRED';
    case ON_REQUEST = 'ON_REQUEST';
    case PAID = 'PAID';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FREE => 'Accès gratuit (sans compte)',
            self::ACCOUNT_REQUIRED => 'Compte requis',
            self::ON_REQUEST => 'Sur demande',
            self::PAID => 'Payant',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FREE => 'success',
            self::ACCOUNT_REQUIRED => 'info',
            self::ON_REQUEST => 'warning',
            self::PAID => 'primary',
        };
    }
}
