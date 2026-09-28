<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PartnerType: string implements HasColor, HasIcon, HasLabel
{
    case BAILLEUR = 'BAILLEUR';
    case INSTITUTIONNEL = 'INSTITUTIONNEL';
    case ENTREPRISE = 'ENTREPRISE';
    case UNIVERSITE = 'UNIVERSITE';
    case TECHNIQUE = 'TECHNIQUE';
    case AUTRE = 'AUTRE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BAILLEUR => 'Bailleur de fonds / Donateur',
            self::INSTITUTIONNEL => 'Partenaire Institutionnel / État',
            self::ENTREPRISE => 'Entreprise / Corporate Sponsor',
            self::UNIVERSITE => 'Université / Centre de Recherche',
            self::TECHNIQUE => 'Partenaire Technique / Opérateur',
            self::AUTRE => 'Autre partenaire',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BAILLEUR => 'success',
            self::INSTITUTIONNEL => 'info',
            self::ENTREPRISE => 'purple',
            self::UNIVERSITE => 'amber',
            self::TECHNIQUE => 'primary',
            self::AUTRE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BAILLEUR => 'heroicon-m-banknotes',
            self::INSTITUTIONNEL => 'heroicon-m-building-library',
            self::ENTREPRISE => 'heroicon-m-building-office-2',
            self::UNIVERSITE => 'heroicon-m-academic-cap',
            self::TECHNIQUE => 'heroicon-m-wrench-screwdriver',
            self::AUTRE => 'heroicon-m-tag',
        };
    }
}
