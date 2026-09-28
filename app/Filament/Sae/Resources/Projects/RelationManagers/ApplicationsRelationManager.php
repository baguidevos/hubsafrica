<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'Candidatures & Évaluations';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clipboard-document-check';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('program.name')
                    ->label('Programme')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->default('Non assignée'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('score')
                    ->label('Note Jury')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 75 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non notée')
                    ->alignCenter(),

                TextColumn::make('decision')
                    ->label('Décision du jury')
                    ->limit(40)
                    ->default('En attente'),

                TextColumn::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
