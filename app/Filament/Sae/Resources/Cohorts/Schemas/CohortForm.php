<?php

namespace App\Filament\Sae\Resources\Cohorts\Schemas;

use App\Enums\CohortStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CohortForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage de la cohorte')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('program_id')
                                ->label('Programme d\'accompagnement')
                                ->relationship('program', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('name')
                                ->label('Nom de la cohorte')
                                ->placeholder('Ex: Promotion 2026 - Cohorte 1')
                                ->required(),

                            TextInput::make('number')
                                ->label('Numéro séquentiel')
                                ->numeric()
                                ->prefix('#')
                                ->placeholder('1'),

                            TextInput::make('capacity')
                                ->label('Capacité maximale')
                                ->numeric()
                                ->suffix('startups')
                                ->placeholder('20'),
                        ]),
                    ]),

                Section::make('Statut & Calendrier')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('status')
                                ->label('Statut de la cohorte')
                                ->options(CohortStatus::class)
                                ->default(CohortStatus::OUVERTE)
                                ->required(),

                            DatePicker::make('start_date')
                                ->label('Date de lancement'),

                            DatePicker::make('end_date')
                                ->label('Date de clôture'),
                        ]),
                    ]),

                Section::make('Objectifs de promotion')
                    ->schema([
                        Textarea::make('objectives')
                            ->label('Objectifs & Thématiques spécifiques')
                            ->rows(3)
                            ->placeholder('Axes stratégiques prioritaires pour cette cohorte...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
