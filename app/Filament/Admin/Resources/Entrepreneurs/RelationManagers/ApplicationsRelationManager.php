<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'Candidatures';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('program.name')
                    ->label('Programme')
                    ->weight('bold'),
                TextColumn::make('project.name')
                    ->label('Projet associé')
                    ->placeholder('—'),
                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('score')
                    ->label('Score')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non notée')
                    ->alignCenter(),
                TextColumn::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ]);
    }
}
