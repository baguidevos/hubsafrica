<?php

namespace App\Filament\Sae\Resources\CohortPhases\Tables;

use App\Enums\ProgramPhaseStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CohortPhasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->badge()
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Jalon / Phase')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cohort.program.name')
                    ->label('Programme')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('starts_on')
                    ->label('Ouverture')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ends_on')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ProgramPhaseStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('starts_on', 'asc');
    }
}
