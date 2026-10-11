<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SaeOriginType: string implements HasColor, HasIcon, HasLabel
{
    case INDEPENDANTE = 'INDEPENDANTE';
    case UNIVERSITAIRE = 'UNIVERSITAIRE';
    case PUBLIQUE = 'PUBLIQUE';
    case ENTREPRISE = 'ENTREPRISE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INDEPENDANTE => 'Structure indépendante',
            self::UNIVERSITAIRE => 'Issue d\'une université / académique',
            self::PUBLIQUE => 'Structure publique / gouvernementale',
            self::ENTREPRISE => 'Corporate / Entreprise privée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INDEPENDANTE => 'primary',
            self::UNIVERSITAIRE => 'amber',
            self::PUBLIQUE => 'info',
            self::ENTREPRISE => 'purple',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::INDEPENDANTE => 'heroicon-m-building-office-2',
            self::UNIVERSITAIRE => 'heroicon-m-academic-cap',
            self::PUBLIQUE => 'heroicon-m-building-library',
            self::ENTREPRISE => 'heroicon-m-briefcase',
        };
    }
}
