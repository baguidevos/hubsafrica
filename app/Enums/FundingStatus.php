<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FundingStatus: string implements HasColor, HasLabel
{
    case DEMANDE = 'DEMANDE';
    case ACCORDE = 'ACCORDE';
    case DECAISSE = 'DECAISSE';
    case INVESTI = 'INVESTI';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEMANDE => 'Demande en cours',
            self::ACCORDE => 'Accordé / Validé',
            self::DECAISSE => 'Décaissé',
            self::INVESTI => 'Investi dans le projet',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DEMANDE => 'warning',
            self::ACCORDE => 'info',
            self::DECAISSE => 'success',
            self::INVESTI => 'primary',
        };
    }
}
