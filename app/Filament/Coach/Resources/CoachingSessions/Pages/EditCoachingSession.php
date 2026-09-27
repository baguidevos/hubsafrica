<?php

namespace App\Filament\Coach\Resources\CoachingSessions\Pages;

use App\Filament\Coach\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoachingSession extends EditRecord
{
    protected static string $resource = CoachingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}


