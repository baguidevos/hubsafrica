<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Pages;

use App\Filament\Sae\Resources\Entrepreneurs\EntrepreneurResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEntrepreneur extends ViewRecord
{
    protected static string $resource = EntrepreneurResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('printFiche')
                ->label('Fiche entrepreneur A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->modalHeading('Fiche signalétique & dossier d\'accompagnement')
                ->modalContent(fn ($record) => view('filament.sae.pages.print-entrepreneur-fiche', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
        ];
    }
}
