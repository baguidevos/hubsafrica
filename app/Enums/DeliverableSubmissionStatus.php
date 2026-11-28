<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliverableSubmissionStatus: string implements HasColor, HasLabel
{
    case BROUILLON = 'BROUILLON';
    case SOUMIS = 'SOUMIS';
    case EN_EVALUATION = 'EN_EVALUATION';
    case VALIDE = 'VALIDE';
    case REJETEE = 'REJETEE';
    case CORRECTION_DEMANDEE = 'CORRECTION_DEMANDEE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMIS => 'Déposé / Soumis',
            self::EN_EVALUATION => 'En cours d\'évaluation',
            self::VALIDE => 'Validé',
            self::REJETEE => 'Rejeté / Non validé',
            self::CORRECTION_DEMANDEE => 'Correction demandée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::SOUMIS => 'warning',
            self::EN_EVALUATION => 'info',
            self::VALIDE => 'success',
            self::REJETEE => 'danger',
            self::CORRECTION_DEMANDEE => 'amber',
        };
    }
}
