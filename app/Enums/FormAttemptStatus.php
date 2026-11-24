<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FormAttemptStatus: string implements HasColor, HasLabel
{
    case EN_COURS = 'EN_COURS';
    case BLOQUE_VALIDATION = 'BLOQUE_VALIDATION';
    case BLOQUE_SPAM = 'BLOQUE_SPAM';
    case SOUMIS = 'SOUMIS';

    public function getLabel(): string
    {
        return match ($this) {
            self::EN_COURS => 'En cours / Abandonné',
            self::BLOQUE_VALIDATION => 'Bloqué (Validation)',
            self::BLOQUE_SPAM => 'Bloqué (Anti-spam)',
            self::SOUMIS => 'Finalisé & Soumis',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::EN_COURS => 'warning',
            self::BLOQUE_VALIDATION => 'danger',
            self::BLOQUE_SPAM => 'gray',
            self::SOUMIS => 'success',
        };
    }
}
