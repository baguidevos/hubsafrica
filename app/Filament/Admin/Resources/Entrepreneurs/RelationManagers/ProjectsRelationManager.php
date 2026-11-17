<?php

namespace App\Filament\Admin\Resources\Entrepreneurs\RelationManagers;

use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $title = 'Projets portés & renseignés';

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
                    ->label('Secteur d\'activité')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('territory')
                    ->label('Territoire')
                    ->placeholder('—'),

                TextColumn::make('team_size')
                    ->label('Équipe')
                    ->suffix(' pers.')
                    ->alignCenter(),

                TextColumn::make('currentLevel.name')
                    ->label('Niveau / Jalon')
                    ->badge()
                    ->color('info')
                    ->default('Niveau 1'),

                TextColumn::make('status')
                    ->label('Statut du projet')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité du projet')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('name')
                                ->label('Nom du projet')
                                ->weight('bold'),
                            TextEntry::make('sector')
                                ->label('Secteur d\'activité')
                                ->badge(),
                            TextEntry::make('territory')
                                ->label('Ville / Territoire'),
                            TextEntry::make('team_size')
                                ->label('Taille de l\'équipe')
                                ->suffix(' personnes'),
                            TextEntry::make('status')
                                ->label('Statut')
                                ->badge(),
                            TextEntry::make('currentLevel.name')
                                ->label('Palier actuel')
                                ->badge(),
                        ]),
                    ]),

                Section::make('Offre & Marché')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('target')
                                ->label('Cible clientèle'),
                            TextEntry::make('market')
                                ->label('Marché visé'),
                        ]),
                        TextEntry::make('value_proposition')
                            ->label('Proposition de valeur')
                            ->placeholder('Non renseignée'),
                        TextEntry::make('problem')
                            ->label('Problème résolu')
                            ->placeholder('Non renseigné'),
                        TextEntry::make('solution')
                            ->label('Solution apportée')
                            ->placeholder('Non renseignée'),
                        TextEntry::make('product_service')
                            ->label('Produits & Services')
                            ->placeholder('Non renseigné'),
                    ]),

                Section::make('Pitch & Description')
                    ->schema([
                        TextEntry::make('pitch')
                            ->label('Pitch Elevator')
                            ->placeholder('Non renseigné'),
                        TextEntry::make('description')
                            ->label('Description détaillée')
                            ->placeholder('Non renseignée'),
                    ]),
            ]);
    }
}
