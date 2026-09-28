<?php

namespace App\Filament\Coach\Resources\CoachingSessions\Pages;

use App\Filament\Coach\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoachingSession extends EditRecord
{
    protected static string $resource = CoachingSessionResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Détails de la séance';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-m-calendar-days';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
