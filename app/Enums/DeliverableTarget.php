<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DeliverableTarget: string implements HasColor, HasIcon, HasLabel
{
    case STARTUP = 'STARTUP';
    case PROGRAMME = 'PROGRAMME';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::STARTUP => 'Startup / Fondateur',
            self::PROGRAMME => 'Programme / SAEI',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::STARTUP => 'primary',
            self::PROGRAMME => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::STARTUP => 'heroicon-m-rocket-launch',
            self::PROGRAMME => 'heroicon-m-building-office',
        };
    }
}
