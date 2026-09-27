<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum FormType: string implements HasLabel, HasColor, HasIcon
{
    case BASELINE = 'BASELINE';
    case DIAGNOSTIC = 'DIAGNOSTIC';
    case SATISFACTION = 'SATISFACTION';
    case ENDLINE = 'ENDLINE';
    case POST_PROGRAMME = 'POST_PROGRAMME';
    case CANDIDATURE = 'CANDIDATURE';
    case ABANDON = 'ABANDON';
    case COLLECTE_DONNEES = 'COLLECTE_DONNEES';
    case AUTRE = 'AUTRE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::BASELINE => 'Diagnostic initial / Entrée (Baseline)',
            self::DIAGNOSTIC => 'Diagnostic de maturité',
            self::SATISFACTION => 'Enquête de satisfaction',
            self::ENDLINE => 'Bilan final / Sortie (Endline)',
            self::POST_PROGRAMME => 'Suivi post-programme (Impact à 6-12 mois)',
            self::CANDIDATURE => 'Formulaire de candidature',
            self::ABANDON => "Questionnaire d'abandon / Sortie prématurée",
            self::COLLECTE_DONNEES => 'Collecte de données périodique',
            self::AUTRE => 'Autre questionnaire',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BASELINE, self::DIAGNOSTIC => 'info',
            self::SATISFACTION => 'warning',
            self::ENDLINE => 'success',
            self::POST_PROGRAMME => 'purple',
            self::CANDIDATURE => 'primary',
            self::ABANDON => 'danger',
            self::COLLECTE_DONNEES => 'teal',
            self::AUTRE => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BASELINE, self::DIAGNOSTIC => 'heroicon-o-arrow-right-start-on-rectangle',
            self::SATISFACTION => 'heroicon-o-face-smile',
            self::ENDLINE => 'heroicon-o-arrow-right-end-on-rectangle',
            self::POST_PROGRAMME => 'heroicon-o-chart-bar-square',
            self::CANDIDATURE => 'heroicon-o-user-plus',
            self::ABANDON => 'heroicon-o-x-circle',
            self::COLLECTE_DONNEES => 'heroicon-o-table-cells',
            self::AUTRE => 'heroicon-o-clipboard-document-list',
        };
    }
}
