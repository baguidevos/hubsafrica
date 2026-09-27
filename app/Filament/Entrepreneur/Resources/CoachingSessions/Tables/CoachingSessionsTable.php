<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions\Tables;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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

                TextColumn::make('coach.name')
                    ->label('Coach référent')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('theme')
                    ->label('Thème de la séance')
                    ->searchable()
                    ->limit(40),

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
                    ->label('Type')
                    ->options(CoachingSessionType::class),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachingSessionStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc');
    }
}
