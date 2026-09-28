<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoachingSessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sessions';

    protected static ?string $title = 'Séances de coaching';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('theme')
            ->columns([
                TextColumn::make('date')
                    ->label('Date & heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('theme')
                    ->label('Thème de travail')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('coach.name')
                    ->label('Coach'),
                TextColumn::make('duration')
                    ->label('Durée')
                    ->suffix(' min')
                    ->alignRight(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('date', 'desc');
    }
}
