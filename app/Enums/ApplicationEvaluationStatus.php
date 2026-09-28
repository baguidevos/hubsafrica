<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApplicationEvaluationStatus: string implements HasColor, HasIcon, HasLabel
{
    case BROUILLON = 'BROUILLON';
    case SOUMIS = 'SOUMIS';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMIS => 'Évaluation soumise / Validée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::SOUMIS => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'heroicon-m-pencil',
            self::SOUMIS => 'heroicon-m-check-badge',
        };
    }
}
