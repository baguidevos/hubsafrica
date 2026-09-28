<?php

namespace App\Filament\Sae\Resources\CohortPhases\Schemas;

use App\Enums\ProgramPhaseStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CohortPhaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage du Jalon')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('cohort_id')
                                ->label('Cohorte ciblée')
                                ->relationship('cohort', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('program_phase_id')
                                ->label('Jalon modèle du programme')
                                ->relationship('programPhase', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Optionnel : relier au jalon théorique du programme'),

                            TextInput::make('name')
                                ->label('Intitulé du jalon')
                                ->placeholder('Ex: Phase 1 : Diagnostic & Boot Camp')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('position')
                                ->label('Ordre chronologique')
                                ->numeric()
                                ->default(1)
                                ->required(),

                            Select::make('status')
                                ->label('Statut du jalon')
                                ->options(ProgramPhaseStatus::class)
                                ->default(ProgramPhaseStatus::A_VENIR)
                                ->required(),

                            DatePicker::make('starts_on')
                                ->label('Date d\'ouverture / début')
                                ->helperText('Date à laquelle ce jalon commence'),

                            DatePicker::make('ends_on')
                                ->label('Date d\'échéance / fin')
                                ->helperText('Date limite pour valider les attendus du jalon'),
                        ]),
                    ]),
            ]);
    }
}
