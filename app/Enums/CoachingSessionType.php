<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CoachingSessionType: string implements HasColor, HasIcon, HasLabel
{
    case INDIVIDUEL = 'INDIVIDUEL';
    case COLLECTIF = 'COLLECTIF';
    case MENTORAT = 'MENTORAT';
    case ATELIER = 'ATELIER';
    case FORMATION = 'FORMATION';
    case SUIVI = 'SUIVI';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INDIVIDUEL => 'Coaching individuel',
            self::COLLECTIF => 'Coaching collectif',
            self::MENTORAT => 'Mentorat',
            self::ATELIER => 'Atelier méthodologique',
            self::FORMATION => 'Formation thématique',
            self::SUIVI => 'Point d\'étape & suivi',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INDIVIDUEL => 'primary',
            self::COLLECTIF => 'success',
            self::MENTORAT => 'danger',
            self::ATELIER => 'warning',
            self::FORMATION => 'info',
            self::SUIVI => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::INDIVIDUEL => 'heroicon-m-user',
            self::COLLECTIF => 'heroicon-m-user-group',
            self::MENTORAT => 'heroicon-m-sparkles',
            self::ATELIER => 'heroicon-m-wrench-screwdriver',
            self::FORMATION => 'heroicon-m-academic-cap',
            self::SUIVI => 'heroicon-m-clipboard-document-check',
        };
    }
}
