<?php

namespace App\Filament\Sae\Resources\CoachingSessions\Schemas;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Models\Entrepreneur;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoachingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage de la séance')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('coach_id')
                                ->label('Coach intervenant')
                                ->relationship('coach', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('project_id')
                                ->label('Projet concerné')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur')
                                ->options(function () {
                                    return Entrepreneur::all()->mapWithKeys(function ($e) {
                                        return [$e->id => "{$e->first_name} {$e->last_name}"];
                                    });
                                })
                                ->searchable()
                                ->preload(),

                            DateTimePicker::make('date')
                                ->label('Date & heure de la séance')
                                ->required()
                                ->default(now()),

                            TextInput::make('duration')
                                ->label('Durée')
                                ->numeric()
                                ->suffix('minutes')
                                ->default(60)
                                ->required(),

                            Select::make('type')
                                ->label('Type d\'accompagnement')
                                ->options(CoachingSessionType::class)
                                ->default(CoachingSessionType::INDIVIDUEL)
                                ->required(),

                            Select::make('status')
                                ->label('Statut de la séance')
                                ->options(CoachingSessionStatus::class)
                                ->default(CoachingSessionStatus::PLANIFIEE)
                                ->required(),

                            TextInput::make('theme')
                                ->label('Thème principal / Sujet abordé')
                                ->placeholder('Ex: Modèle économique, Business Plan, Pitch, Levée de fonds')
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Contenu pédagogique & Compte-rendu')
                    ->schema([
                        Textarea::make('objectives')
                            ->label('Objectifs de la séance')
                            ->rows(3)
                            ->placeholder('Quels étaient les livrables ou compétences visés ?')
                            ->columnSpanFull(),

                        Textarea::make('report')
                            ->label('Compte-rendu & Échanges')
                            ->rows(4)
                            ->placeholder('Synthèse des discussions, difficultés remontées et conseils donnés')
                            ->columnSpanFull(),

                        Textarea::make('next_actions')
                            ->label('Prochaines actions convenues (Roadmap)')
                            ->rows(3)
                            ->placeholder('Engagements pris par l\'entrepreneur pour la prochaine séance')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
