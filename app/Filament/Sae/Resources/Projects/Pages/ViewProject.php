<?php

namespace App\Filament\Sae\Resources\Projects\Pages;

use App\Filament\Sae\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('printFiche')
                ->label('Fiche projet A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->modalHeading('Dossier & Fiche de synthèse projet')
                ->modalContent(fn ($record) => view('filament.sae.pages.print-project-fiche', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
        ];
    }
}
