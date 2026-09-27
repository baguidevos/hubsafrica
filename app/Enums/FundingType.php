<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FundingType: string implements HasLabel, HasColor
{
    case GRANT = 'GRANT';
    case SUBVENTION = 'SUBVENTION';
    case LOAN = 'LOAN';
    case PRET = 'PRET';
    case EQUITY = 'EQUITY';
    case FONDS_PROPRES = 'FONDS_PROPRES';
    case SUBSIDY = 'SUBSIDY';
    case DON = 'DON';
    case PRIZE = 'PRIZE';
    case PRIX = 'PRIX';
    case OTHER = 'OTHER';
    case AUTRE = 'AUTRE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::GRANT, self::SUBVENTION => 'Subvention',
            self::LOAN, self::PRET => 'Prêt / Dette d\'honneur',
            self::EQUITY, self::FONDS_PROPRES => 'Fonds propres / Capital',
            self::SUBSIDY, self::DON => 'Aide publique / Don',
            self::PRIZE, self::PRIX => 'Prix / Concours',
            self::OTHER, self::AUTRE => 'Autre source',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::GRANT, self::SUBVENTION => 'success',
            self::LOAN, self::PRET => 'info',
            self::EQUITY, self::FONDS_PROPRES => 'primary',
            self::SUBSIDY, self::DON => 'teal',
            self::PRIZE, self::PRIX => 'amber',
            self::OTHER, self::AUTRE => 'gray',
        };
    }
}
