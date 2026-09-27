<?php

namespace App\Filament\Sae\Resources\Programs\Schemas;

use App\Enums\ProgramStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Présentation du programme')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du programme')
                                ->placeholder('Ex: Accélération Impact 2026')
                                ->required(),

                            Select::make('level_id')
                                ->label('Niveau ciblé')
                                ->relationship('level', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('target')
                                ->label('Public / Profil cible')
                                ->placeholder('Ex: Startups Seed / Early-stage, AgriTech, GreenTech'),

                            TextInput::make('capacity')
                                ->label('Capacité totale d\'accueil')
                                ->numeric()
                                ->suffix('startups'),

                            Select::make('status')
                                ->label('Statut du programme')
                                ->options(ProgramStatus::class)
                                ->default(ProgramStatus::OUVERT_CANDIDATURES)
                                ->required()
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description générale')
                                ->placeholder('Présentation détaillée du programme...')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('objectives')
                                ->label('Objectifs & Livrables visés')
                                ->placeholder('Objectifs de croissance, levée de fonds, structuration...')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('eligibility')
                                ->label('Critères d\'éligibilité')
                                ->placeholder('Conditions à remplir par les entrepreneurs candidats...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Calendrier & Candidatures')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('registration_start')
                                ->label('Ouverture des candidatures'),

                            DatePicker::make('registration_end')
                                ->label('Clôture des candidatures'),

                            DatePicker::make('start_date')
                                ->label('Date de lancement effectif'),

                            DatePicker::make('end_date')
                                ->label('Date de clôture prévisionnelle'),
                        ]),
                    ]),
            ]);
    }
}
