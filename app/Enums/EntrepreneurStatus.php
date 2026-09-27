<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EntrepreneurStatus: string implements HasLabel, HasColor
{
    case ACTIVE = 'ACTIVE';
    case ABANDONNE = 'ABANDONNE';
    case DIPLOME = 'DIPLOME';
    case SUSPENDU = 'SUSPENDU';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::ABANDONNE => 'Abandonné',
            self::DIPLOME => 'Diplômé',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ABANDONNE => 'danger',
            self::DIPLOME => 'emerald',
            self::SUSPENDU => 'warning',
        };
    }
}
