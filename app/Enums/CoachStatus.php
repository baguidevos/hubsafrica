<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CoachStatus: string implements HasLabel, HasColor
{
    case ACTIF = 'ACTIF';
    case ACTIVE = 'ACTIVE';
    case INACTIF = 'INACTIF';
    case INACTIVE = 'INACTIVE';
    case SUSPENDU = 'SUSPENDU';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIF, self::ACTIVE => 'Actif',
            self::INACTIF, self::INACTIVE => 'Inactif',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIF, self::ACTIVE => 'success',
            self::INACTIF, self::INACTIVE => 'gray',
            self::SUSPENDU => 'danger',
        };
    }
}
