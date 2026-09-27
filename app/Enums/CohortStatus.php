<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CohortStatus: string implements HasLabel, HasColor
{
    case OUVERTE = 'OUVERTE';
    case EN_COURS = 'EN_COURS';
    case CLOTUREE = 'CLOTUREE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::OUVERTE => 'Ouverte / Inscriptions',
            self::EN_COURS => 'En cours d\'incubation',
            self::CLOTUREE => 'Clôturée',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::OUVERTE => 'info',
            self::EN_COURS => 'success',
            self::CLOTUREE => 'gray',
        };
    }
}
