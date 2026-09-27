<?php

namespace App\Filament\Coach\Resources\CoachingSessions\Schemas;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Models\Entrepreneur;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
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
                Hidden::make('coach_id')
                    ->default(fn () => auth()->user()?->coachProfile?->id),

                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->coachProfile?->sae_id),

                Section::make('Cadrage de la séance')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('project_id')
                                ->label('Projet accompagné')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

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
                                ->label('Date & heure')
                                ->required()
                                ->default(now()),

                            TextInput::make('duration')
                                ->label('Durée')
                                ->numeric()
                                ->suffix('min')
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
                                ->label('Thème de travail')
                                ->placeholder('Ex: Pitch deck, Stratégie go-to-market')
                                ->columnSpan(3),
                        ]),
                    ]),

                Section::make('Contenu pédagogique & Feuille de route')
                    ->schema([
                        Textarea::make('objectives')
                            ->label('Objectifs de la séance')
                            ->rows(3)
                            ->placeholder('Quels étaient les livrables ou compétences visés ?')
                            ->columnSpanFull(),

                        Textarea::make('report')
                            ->label('Compte-rendu & Recommandations')
                            ->rows(4)
                            ->placeholder('Synthèse des échanges, points de vigilance et préconisations')
                            ->columnSpanFull(),

                        Textarea::make('next_actions')
                            ->label('Prochaines actions convenues (Roadmap)')
                            ->rows(3)
                            ->placeholder('Engagements pris par l\'entrepreneur pour la prochaine étape')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
