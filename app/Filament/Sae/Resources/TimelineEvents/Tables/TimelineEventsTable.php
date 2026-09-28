<?php

namespace App\Filament\Sae\Resources\TimelineEvents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TimelineEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date & Heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'SESSION_COACHING' => 'Coaching',
                        'LIVRABLE_DEPOSE' => 'Livrable déposé',
                        'LIVRABLE_VALIDE' => 'Livrable validé',
                        'FINANCEMENT' => 'Financement',
                        'STATUT_PROJET' => 'Statut projet',
                        'PALIER_FRANCHI' => 'Palier franchi',
                        'APPLICATION' => 'Candidature',
                        'NOTE' => 'Note',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'SESSION_COACHING',
                        'warning' => 'LIVRABLE_DEPOSE',
                        'success' => 'LIVRABLE_VALIDE',
                        'emerald' => 'FINANCEMENT',
                        'info' => 'PALIER_FRANCHI',
                        'gray' => 'NOTE',
                    ])
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Événement')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->searchable(['first_name', 'last_name'])
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'SESSION_COACHING' => 'Séance de coaching',
                        'LIVRABLE_DEPOSE' => 'Dépôt de livrable',
                        'LIVRABLE_VALIDE' => 'Validation de livrable',
                        'FINANCEMENT' => 'Financement',
                        'STATUT_PROJET' => 'Statut projet',
                        'PALIER_FRANCHI' => 'Palier franchi',
                    ]),

                SelectFilter::make('project_id')
                    ->label('Projet')
                    ->relationship('project', 'name'),
            ])
            ->recordActions([
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
