<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeliverableSubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';
    protected static ?string $title = 'Livrables & Dépôts';
    protected static string | \BackedEnum | null $icon = 'heroicon-o-document-check';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('definition.name')
                    ->label('Livrable attendu')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('version')
                    ->label('Version')
                    ->formatStateUsing(fn ($state) => "v{$state}")
                    ->badge()
                    ->color('gray'),
                TextColumn::make('submitted_at')
                    ->label('Date de dépôt')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Fichier')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->file_path)),
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
