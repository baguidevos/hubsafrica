<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FormUnlockStatus: string implements HasLabel, HasColor, HasIcon
{
    case EN_ATTENTE = 'EN_ATTENTE';
    case SOUMIS = 'SOUMIS';
    case EN_RETARD = 'EN_RETARD';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::SOUMIS => 'Soumis / Complété',
            self::EN_RETARD => 'En retard',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::EN_ATTENTE => 'warning',
            self::SOUMIS => 'success',
            self::EN_RETARD => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::EN_ATTENTE => 'heroicon-o-clock',
            self::SOUMIS => 'heroicon-o-check-circle',
            self::EN_RETARD => 'heroicon-o-exclamation-triangle',
        };
    }
}
