<?php

namespace App\Filament\Sae\Resources\CoachingSessions\Pages;

use App\Filament\Sae\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoachingSessions extends ListRecords
{
    protected static string $resource = CoachingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('calendar')
                ->label('Vue Calendrier')
                ->icon('heroicon-o-calendar')
                ->color('gray')
                ->url(fn () => url(filament()->getCurrentPanel()->getId() . '/coaching-calendar-page')),
            CreateAction::make()
                ->label('Nouvelle séance')
                ->slideOver(),
        ];
    }
}
