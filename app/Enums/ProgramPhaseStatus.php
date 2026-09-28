<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ProgramPhaseStatus: string implements HasColor, HasIcon, HasLabel
{
    case A_VENIR = 'A_VENIR';
    case EN_COURS = 'EN_COURS';
    case TERMINEE = 'TERMINEE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::A_VENIR => 'À venir',
            self::EN_COURS => 'En cours',
            self::TERMINEE => 'Terminée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::A_VENIR => 'gray',
            self::EN_COURS => 'info',
            self::TERMINEE => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::A_VENIR => 'heroicon-m-clock',
            self::EN_COURS => 'heroicon-m-arrow-path',
            self::TERMINEE => 'heroicon-m-check-circle',
        };
    }
}
