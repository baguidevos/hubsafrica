<?php

namespace App\Filament\Sae\Resources\Programs\Pages;

use App\Filament\Sae\Resources\Programs\ProgramResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Programme créé avec succès !')
            ->body('Vous pouvez maintenant configurer ses phases, critères jury et bailleurs dans les onglets ci-dessus.')
            ->success();
    }
}
