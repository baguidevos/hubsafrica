<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ApplicationStatus: string implements HasColor, HasIcon, HasLabel
{
    case BROUILLON = 'BROUILLON';
    case SOUMISE = 'SOUMISE';
    case EN_EXAMEN = 'EN_EXAMEN';
    case ACCEPTEE = 'ACCEPTEE';
    case REFUSEE = 'REFUSEE';
    case REORIENTEE = 'REORIENTEE';
    case EN_ATTENTE = 'EN_ATTENTE';
    case RETIREE = 'RETIREE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMISE => 'Déposée / Soumise',
            self::EN_EXAMEN => 'En examen',
            self::ACCEPTEE => 'Acceptée / Retenue',
            self::REFUSEE => 'Refusée',
            self::REORIENTEE => 'Réorientée',
            self::EN_ATTENTE => 'Liste d\'attente',
            self::RETIREE => 'Retirée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::SOUMISE => 'warning',
            self::EN_EXAMEN => 'info',
            self::ACCEPTEE => 'success',
            self::REFUSEE => 'danger',
            self::REORIENTEE => 'amber',
            self::EN_ATTENTE => 'purple',
            self::RETIREE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'heroicon-m-document',
            self::SOUMISE => 'heroicon-m-paper-airplane',
            self::EN_EXAMEN => 'heroicon-m-magnifying-glass',
            self::ACCEPTEE => 'heroicon-m-check-badge',
            self::REFUSEE => 'heroicon-m-x-circle',
            self::REORIENTEE => 'heroicon-m-arrow-path',
            self::EN_ATTENTE => 'heroicon-m-clock',
            self::RETIREE => 'heroicon-m-archive-box-x-mark',
        };
    }
}
