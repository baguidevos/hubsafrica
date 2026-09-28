<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IndicatorStatus: string implements HasColor, HasLabel
{
    case EN_BONNE_VOIE = 'EN_BONNE_VOIE';
    case VIGILANCE = 'VIGILANCE';
    case RETARD = 'RETARD';
    case ATTEINT = 'ATTEINT';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EN_BONNE_VOIE => 'En bonne voie',
            self::VIGILANCE => 'Vigilance requise',
            self::RETARD => 'En retard / Alerte',
            self::ATTEINT => 'Objectif atteint',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EN_BONNE_VOIE => 'success',
            self::VIGILANCE => 'warning',
            self::RETARD => 'danger',
            self::ATTEINT => 'primary',
        };
    }
}
