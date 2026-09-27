<?php

namespace App\Filament\Sae\Resources\CoachingSessions\Tables;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoachingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date & heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('theme')
                    ->label('Thème')
                    ->searchable()
                    ->limit(35)
                    ->weight('medium'),

                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Durée')
                    ->suffix(' min')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de séance')
                    ->options(CoachingSessionType::class),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachingSessionStatus::class),

                SelectFilter::make('coach_id')
                    ->label('Coach')
                    ->relationship('coach', 'name'),
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
            ->defaultSort('date', 'desc');
    }
}
