<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum DeliverableSubmissionStatus: string implements HasColor, HasLabel
{
    case BROUILLON = 'BROUILLON';
    case SOUMIS = 'SOUMIS';
    case SOUMISE = 'SOUMISE';
    case EN_EVALUATION = 'EN_EVALUATION';
    case EN_EXAMEN = 'EN_EXAMEN';
    case VALIDE = 'VALIDE';
    case VALIDEE = 'VALIDEE';
    case NON_VALIDE = 'NON_VALIDE';
    case REJETEE = 'REJETEE';
    case CORRECTION_DEMANDEE = 'CORRECTION_DEMANDEE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::SOUMIS, self::SOUMISE => 'Déposé / Soumis',
            self::EN_EVALUATION, self::EN_EXAMEN => 'En cours d\'évaluation',
            self::VALIDE, self::VALIDEE => 'Validé',
            self::NON_VALIDE, self::REJETEE => 'Rejeté / Non validé',
            self::CORRECTION_DEMANDEE => 'Correction demandée',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BROUILLON => 'gray',
            self::SOUMIS, self::SOUMISE => 'warning',
            self::EN_EVALUATION, self::EN_EXAMEN => 'info',
            self::VALIDE, self::VALIDEE => 'success',
            self::NON_VALIDE, self::REJETEE => 'danger',
            self::CORRECTION_DEMANDEE => 'amber',
        };
    }
}
