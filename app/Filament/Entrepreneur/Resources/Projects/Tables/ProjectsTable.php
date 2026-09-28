<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Mon Projet')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sector')
                    ->label('Secteur')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('territory')
                    ->label('Territoire')
                    ->searchable(),

                TextColumn::make('currentLevel.name')
                    ->label('Niveau / Jalon')
                    ->badge()
                    ->color('info')
                    ->default('Niveau 1'),

                TextColumn::make('team_size')
                    ->label('Équipe')
                    ->suffix(' pers.')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('name');
    }
}
