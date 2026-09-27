<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProjectStatus: string implements HasLabel, HasColor, HasIcon
{
    case ACTIF = 'ACTIF';
    case BLOQUE = 'BLOQUE';
    case STAGNANT = 'STAGNANT';
    case SORTI = 'SORTI';
    case ABANDONNE = 'ABANDONNE';
    case DIPLOME = 'DIPLOME';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIF => 'Actif',
            self::BLOQUE => 'Bloqué',
            self::STAGNANT => 'Stagnant',
            self::SORTI => 'Sorti',
            self::ABANDONNE => 'Abandonné',
            self::DIPLOME => 'Diplômé',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIF => 'success',
            self::BLOQUE => 'danger',
            self::STAGNANT => 'warning',
            self::SORTI => 'gray',
            self::ABANDONNE => 'gray',
            self::DIPLOME => 'emerald',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::ACTIF => 'heroicon-m-check-circle',
            self::BLOQUE => 'heroicon-m-exclamation-triangle',
            self::STAGNANT => 'heroicon-m-clock',
            self::SORTI => 'heroicon-m-arrow-right-on-rectangle',
            self::ABANDONNE => 'heroicon-m-no-symbol',
            self::DIPLOME => 'heroicon-m-academic-cap',
        };
    }
}
