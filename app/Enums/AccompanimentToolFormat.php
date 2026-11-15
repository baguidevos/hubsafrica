<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccompanimentToolFormat: string implements HasColor, HasLabel
{
    case DOCUMENT = 'DOCUMENT';
    case DRIVE_LINK = 'DRIVE_LINK';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DOCUMENT => 'Document téléchargeable',
            self::DRIVE_LINK => 'Lien Google Drive',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DOCUMENT => 'info',
            self::DRIVE_LINK => 'success',
        };
    }
}
