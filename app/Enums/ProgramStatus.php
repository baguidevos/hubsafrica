<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ProgramStatus: string implements HasLabel, HasColor
{
    case BROUILLON = 'BROUILLON';
    case OUVERT_CANDIDATURES = 'OUVERT_CANDIDATURES';
    case CANDIDATURES_FERMEES = 'CANDIDATURES_FERMEES';
    case SELECTION = 'SELECTION';
    case LANCE = 'LANCE';
    case ACTIF = 'ACTIF';
    case SUIVI = 'SUIVI';
    case CLOTURE = 'CLOTURE';
    case ARCHIVE = 'ARCHIVE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::OUVERT_CANDIDATURES => 'Appel à candidatures ouvert',
            self::CANDIDATURES_FERMEES => 'Candidatures closes',
            self::SELECTION => 'Comité de sélection en cours',
            self::LANCE, self::ACTIF => 'Actif / En cours',
            self::SUIVI => 'Phase de suivi',
            self::CLOTURE => 'Clôturé',
            self::ARCHIVE => 'Archivé',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::OUVERT_CANDIDATURES => 'primary',
            self::CANDIDATURES_FERMEES => 'amber',
            self::SELECTION => 'info',
            self::LANCE, self::ACTIF => 'success',
            self::SUIVI => 'teal',
            self::CLOTURE, self::ARCHIVE => 'gray',
        };
    }
}
