<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum IndicatorCategory: string implements HasLabel, HasColor
{
    case PORTEE = 'PORTEE';
    case INCLUSION = 'INCLUSION';
    case PORTEFEUILLE = 'PORTEFEUILLE';
    case MISE_EN_OEUVRE = 'MISE_EN_OEUVRE';
    case PROGRESSION = 'PROGRESSION';
    case LIVRABLES = 'LIVRABLES';
    case FINANCEMENT = 'FINANCEMENT';
    case QUALITE = 'QUALITE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PORTEE => 'Portée & Audience',
            self::INCLUSION => 'Inclusion & Genre',
            self::PORTEFEUILLE => 'Portefeuille startups',
            self::MISE_EN_OEUVRE => 'Mise en œuvre des activités',
            self::PROGRESSION => 'Progression dans le parcours',
            self::LIVRABLES => 'Livrables & Assiduité',
            self::FINANCEMENT => 'Financement & Levée de fonds',
            self::QUALITE => 'Gouvernance & Qualité',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PORTEE => 'primary',
            self::INCLUSION => 'pink',
            self::PORTEFEUILLE => 'indigo',
            self::MISE_EN_OEUVRE => 'info',
            self::PROGRESSION => 'success',
            self::LIVRABLES => 'amber',
            self::FINANCEMENT => 'emerald',
            self::QUALITE => 'gray',
        };
    }
}
