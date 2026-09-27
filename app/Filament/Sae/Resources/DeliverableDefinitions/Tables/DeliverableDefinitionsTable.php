<?php

namespace App\Filament\Sae\Resources\DeliverableDefinitions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DeliverableDefinitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé du livrable')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.name')
                    ->label('Programme')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable()
                    ->default('Tous programmes'),

                TextColumn::make('level.name')
                    ->label('Niveau')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable()
                    ->default('Transversal'),

                IconColumn::make('is_required')
                    ->label('Obligatoire')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('passing_score')
                    ->label('Seuil validation')
                    ->suffix(' / 100')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('deadline')
                    ->label('Échéance')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Aucune')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),

                SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),

                TernaryFilter::make('is_required')
                    ->label('Obligatoire'),
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
            ->defaultSort('name');
    }
}
