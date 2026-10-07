<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliverableResponsibleRole: string implements HasColor, HasLabel
{
    case FONDATEUR = 'FONDATEUR';
    case COACH = 'COACH';
    case COORDINATEUR = 'COORDINATEUR';
    case FORMATEUR = 'FORMATEUR';
    case CHARGE_ME = 'CHARGE_ME';
    case SAEI = 'SAEI';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FONDATEUR => 'Fondateur',
            self::COACH => 'Coach',
            self::COORDINATEUR => 'Coordinateur SAEI',
            self::FORMATEUR => 'Formateur',
            self::CHARGE_ME => 'Chargé M&E',
            self::SAEI => 'Équipe SAEI',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FONDATEUR => 'primary',
            self::COACH => 'success',
            self::COORDINATEUR => 'warning',
            self::FORMATEUR => 'info',
            self::CHARGE_ME => 'purple',
            self::SAEI => 'gray',
        };
    }
}
