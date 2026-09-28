<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum DocumentCategory: string implements HasColor, HasIcon, HasLabel
{
    case CANDIDATURE = 'CANDIDATURE';
    case LIVRABLE = 'LIVRABLE';
    case ADMINISTRATIF = 'ADMINISTRATIF';
    case DATAROOM = 'DATAROOM';
    case CONTRAT = 'CONTRAT';
    case AUTRE = 'AUTRE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CANDIDATURE => 'Pièce de candidature',
            self::LIVRABLE => 'Livrable d\'accompagnement',
            self::ADMINISTRATIF => 'Document administratif',
            self::DATAROOM => 'Data Room / Investisseur',
            self::CONTRAT => 'Convention / Contrat',
            self::AUTRE => 'Autre document',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CANDIDATURE => 'info',
            self::LIVRABLE => 'success',
            self::ADMINISTRATIF => 'warning',
            self::DATAROOM => 'purple',
            self::CONTRAT => 'amber',
            self::AUTRE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::CANDIDATURE => 'heroicon-m-document-text',
            self::LIVRABLE => 'heroicon-m-folder-arrow-down',
            self::ADMINISTRATIF => 'heroicon-m-shield-check',
            self::DATAROOM => 'heroicon-m-circle-stack',
            self::CONTRAT => 'heroicon-m-pencil-square',
            self::AUTRE => 'heroicon-m-paper-clip',
        };
    }
}
