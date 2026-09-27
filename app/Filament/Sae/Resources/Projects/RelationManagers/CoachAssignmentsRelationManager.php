<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoachAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';
    protected static ?string $title = 'Coachs assignés';
    protected static string | \BackedEnum | null $icon = 'heroicon-o-academic-cap';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('coach.name')
                    ->label('Coach')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('coach.email')
                    ->label('Email'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('assigned_at')
                    ->label('Affecté le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ]);
    }
}
