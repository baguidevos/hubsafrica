<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CoachingSessionStatus: string implements HasColor, HasIcon, HasLabel
{
    case PLANIFIEE = 'PLANIFIEE';
    case REALISEE = 'REALISEE';
    case ANNULEE = 'ANNULEE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PLANIFIEE => 'Planifiée',
            self::REALISEE => 'Réalisée',
            self::ANNULEE => 'Annulée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PLANIFIEE => 'warning',
            self::REALISEE => 'success',
            self::ANNULEE => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PLANIFIEE => 'heroicon-m-clock',
            self::REALISEE => 'heroicon-m-check-circle',
            self::ANNULEE => 'heroicon-m-x-circle',
        };
    }
}
