<?php

namespace App\Filament\Sae\Resources\Cohorts\Pages;

use App\Filament\Sae\Resources\Cohorts\CohortResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateCohort extends CreateRecord
{
    protected static string $resource = CohortResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Cohorte créée avec succès !')
            ->body('Vous pouvez maintenant synchroniser et ajuster les jalons réels de la promotion dans l\'onglet « Jalons réels & Calendrier » ci-dessus.')
            ->success();
    }
}
