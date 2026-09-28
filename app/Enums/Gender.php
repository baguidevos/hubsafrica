<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Gender: string implements HasColor, HasLabel
{
    case F = 'F';
    case M = 'M';
    case AUTRE = 'AUTRE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::F => 'Féminin',
            self::M => 'Masculin',
            self::AUTRE => 'Autre',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::F => 'primary',
            self::M => 'info',
            self::AUTRE => 'gray',
        };
    }
}
