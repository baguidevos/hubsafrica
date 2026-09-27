<?php

namespace App\Filament\Sae\Resources\Levels\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Palier / Niveau du parcours CUBE')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du niveau')
                                ->placeholder('Ex: Niveau 1 - Idéation & Cadrage')
                                ->required(),

                            TextInput::make('order')
                                ->label('Rang / Séquence')
                                ->numeric()
                                ->default(1)
                                ->required(),

                            TextInput::make('color')
                                ->label('Code couleur')
                                ->placeholder('#3b82f6'),

                            TextInput::make('min_average')
                                ->label('Moyenne minimale requise pour franchir')
                                ->numeric()
                                ->suffix('/ 100')
                                ->placeholder('70'),

                            Textarea::make('description')
                                ->label('Description & Objectifs du niveau')
                                ->rows(3)
                                ->placeholder('Compétences clés, livrables attendus et jalons de validation...')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
