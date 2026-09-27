<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions\Pages;

use App\Filament\Entrepreneur\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoachingSessions extends ListRecords
{
    protected static string $resource = CoachingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}


