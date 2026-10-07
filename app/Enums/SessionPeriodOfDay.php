<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SessionPeriodOfDay: string implements HasLabel
{
    case MATIN = 'MATIN';
    case APRES_MIDI = 'APRES_MIDI';
    case JOURNEE_COMPLETE = 'JOURNEE_COMPLETE';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::MATIN => 'Matin',
            self::APRES_MIDI => 'Après-midi',
            self::JOURNEE_COMPLETE => 'Journée complète',
        };
    }
}
