<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CoachAssignmentStatus: string implements HasLabel, HasColor
{
    case ACTIF = 'ACTIF';
    case TERMINE = 'TERMINE';
    case SUSPENDU = 'SUSPENDU';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIF => 'Actif',
            self::TERMINE => 'Terminé',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIF => 'success',
            self::TERMINE => 'gray',
            self::SUSPENDU => 'warning',
        };
    }
}
