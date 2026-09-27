<?php

namespace App\Filament\Sae\Resources\Applications\Schemas;

use App\Enums\ApplicationStatus;
use App\Models\Entrepreneur;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidat & Programme visé')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('program_id')
                                ->label('Programme d\'accompagnement')
                                ->relationship('program', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('cohort_id')
                                ->label('Cohorte cible')
                                ->relationship('cohort', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur candidat')
                                ->options(fn () => Entrepreneur::all()->mapWithKeys(fn ($e) => [$e->id => "{$e->first_name} {$e->last_name}"]))
                                ->required()
                                ->searchable()
                                ->preload(),

                            Select::make('project_id')
                                ->label('Projet associé')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload(),

                            DateTimePicker::make('submitted_at')
                                ->label('Date de soumission')
                                ->default(now()),
                        ]),
                    ]),

                Section::make('Évaluation & Décision du comité')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut de la candidature')
                                ->options(ApplicationStatus::class)
                                ->default(ApplicationStatus::SOUMISE)
                                ->required(),

                            TextInput::make('score')
                                ->label('Note / Score d\'évaluation')
                                ->numeric()
                                ->suffix('/ 100'),

                            TextInput::make('decision')
                                ->label('Intitulé de la décision')
                                ->placeholder('Ex: Retenu pour la cohorte 2026'),

                            DateTimePicker::make('reviewed_at')
                                ->label('Date du comité d\'examen'),
                        ]),

                        Textarea::make('comment')
                            ->label('Avis du jury & Commentaires')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('rejection_reason')
                            ->label('Motif de refus ou réorientation')
                            ->rows(2)
                            ->visible(fn ($get) => in_array($get('status'), [ApplicationStatus::REFUSEE, ApplicationStatus::REORIENTEE, 'REFUSEE', 'REORIENTEE']))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
