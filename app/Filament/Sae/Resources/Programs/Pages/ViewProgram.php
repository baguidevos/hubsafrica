<?php

namespace App\Filament\Sae\Resources\Programs\Pages;

use App\Filament\Sae\Resources\Programs\ProgramResource;
use App\Models\Program;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('toggleActive')
                ->label(fn (Program $record): string => $record->is_active ? 'Désactiver le programme' : 'Activer le programme')
                ->icon(fn (Program $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                ->color(fn (Program $record): string => $record->is_active ? 'warning' : 'success')
                ->requiresConfirmation()
                ->modalHeading(fn (Program $record): string => $record->is_active ? "Désactiver « {$record->name} »" : "Activer « {$record->name} »")
                ->modalDescription(fn (Program $record): string => $record->is_active
                    ? 'Ce programme sera masqué sur le portail public, mais restera intégralement accessible dans votre espace de gestion SAE.'
                    : 'Ce programme sera de nouveau visible pour les entrepreneurs sur le portail public.')
                ->action(function (Program $record): void {
                    $newState = ! $record->is_active;
                    $record->update(['is_active' => $newState]);

                    Notification::make()
                        ->title($newState ? 'Programme activé' : 'Programme désactivé')
                        ->body($newState
                            ? "Le programme « {$record->name} » est désormais visible sur le portail public."
                            : "Le programme « {$record->name} » a été masqué du portail public.")
                        ->success()
                        ->send();
                }),

            EditAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }

    protected function resolveRecord(int|string $key): Model
    {
        /** @var Program $program */
        $program = parent::resolveRecord($key);

        return $program
            ->load(['level', 'sae'])
            ->loadCount(['cohorts', 'applications', 'phases', 'partners']);
    }
}
