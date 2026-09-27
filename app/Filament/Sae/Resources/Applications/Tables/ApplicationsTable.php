<?php

namespace App\Filament\Sae\Resources\Applications\Tables;

use App\Enums\ApplicationStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur candidat')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('program.name')
                    ->label('Programme')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->default('Non assignée'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('score')
                    ->label('Score')
                    ->suffix(' / 100')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ApplicationStatus::class),

                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),

                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),
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
            ->defaultSort('submitted_at', 'desc');
    }
}
