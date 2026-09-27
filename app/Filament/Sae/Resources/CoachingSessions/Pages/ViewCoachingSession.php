<?php

namespace App\Filament\Sae\Resources\CoachingSessions\Pages;

use App\Filament\Sae\Resources\CoachingSessions\CoachingSessionResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoachingSession extends ViewRecord
{
    protected static string $resource = CoachingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('printReport')
                ->label('Fiche Compte-rendu A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->modalHeading('Compte-rendu de séance de coaching')
                ->modalContent(fn ($record) => view('filament.sae.pages.print-session-report', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
        ];
    }
}
