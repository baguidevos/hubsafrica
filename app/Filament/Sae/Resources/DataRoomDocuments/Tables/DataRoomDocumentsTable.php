<?php

namespace App\Filament\Sae\Resources\DataRoomDocuments\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DataRoomDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Document')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->placeholder('Général')
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'ADMINISTRATIF' => 'Administratif',
                        'JURIDIQUE' => 'Juridique',
                        'FINANCE' => 'Finance',
                        'COMMERCIAL' => 'Commercial',
                        'TECHNIQUE' => 'Technique',
                        'RH' => 'RH',
                        default => $state,
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'RECU' => 'En attente',
                        'VALIDE' => 'Validé',
                        'REJETE' => 'Rejeté',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'RECU',
                        'success' => 'VALIDE',
                        'danger' => 'REJETE',
                    ])
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Ajouté le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options([
                        'ADMINISTRATIF' => 'Administratif',
                        'JURIDIQUE' => 'Juridique',
                        'FINANCE' => 'Finance',
                        'COMMERCIAL' => 'Commercial',
                        'TECHNIQUE' => 'Technique',
                        'RH' => 'RH',
                        'AUTRE' => 'Autre',
                    ]),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'RECU' => 'En attente',
                        'VALIDE' => 'Validé',
                        'REJETE' => 'Rejeté',
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => $record->file_path ? asset('storage/'.$record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->file_path)),

                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
