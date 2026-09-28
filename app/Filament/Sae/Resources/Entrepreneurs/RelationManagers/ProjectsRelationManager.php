<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $title = 'Projets portés';

    protected static string|BackedEnum|null $icon = 'heroicon-o-light-bulb';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du projet')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('sector')
                    ->label('Secteur')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('territory')
                    ->label('Territoire')
                    ->placeholder('—'),
                TextColumn::make('currentLevel.name')
                    ->label('Niveau / Jalon')
                    ->badge()
                    ->color('info')
                    ->default('Niveau 1'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ]);
    }
}
