<?php

namespace App\Filament\Sae\Resources\DeliverableDefinitions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliverableDefinitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage & Programme')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('program_id')
                                ->label('Programme d\'accompagnement')
                                ->relationship('program', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('level_id')
                                ->label('Niveau / Phase')
                                ->relationship('level', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('name')
                                ->label('Intitulé du livrable attendu')
                                ->placeholder('Ex: BMC validé, Plan financier prévisionnel, Pitch deck')
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description générale')
                                ->placeholder('Décrivez brièvement la finalité de ce livrable...')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('instructions')
                                ->label('Consignes & Grille méthodologique')
                                ->placeholder('Détaillez les exigences, les sections attendues et les critères qualité...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Critères d\'évaluation & Exigences')
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('max_score')
                                ->label('Note maximale')
                                ->numeric()
                                ->default(100)
                                ->suffix('pts')
                                ->required(),

                            TextInput::make('passing_score')
                                ->label('Note minimale requise')
                                ->numeric()
                                ->default(70)
                                ->suffix('pts')
                                ->required(),

                            TextInput::make('max_attempts')
                                ->label('Tentatives maximales')
                                ->numeric()
                                ->default(3)
                                ->required(),

                            DateTimePicker::make('deadline')
                                ->label('Échéance de soumission')
                                ->default(null),

                            Toggle::make('is_required')
                                ->label('Livrable obligatoire pour franchir le palier')
                                ->default(true)
                                ->inline(false),
                        ]),
                    ]),
            ]);
    }
}
