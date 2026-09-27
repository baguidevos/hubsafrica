<?php

namespace App\Filament\Coach\Resources\Projects\Tables;

use App\Enums\ProjectStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Projet')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur porteur')
                    ->searchable(['first_name', 'last_name'])
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
                    ->label('Jalon CUBE')
                    ->badge()
                    ->color('info')
                    ->default('Niveau 1'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ProjectStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
