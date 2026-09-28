<?php

namespace App\Filament\Coach\Resources\CoachingSessions\Pages;

use App\Filament\Coach\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCoachingSession extends CreateRecord
{
    protected static string $resource = CoachingSessionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
