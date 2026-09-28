<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions\Schemas;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
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
                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->sae_id),

                Hidden::make('entrepreneur_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->id),

                Section::make('Détails de la séance')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet concerné')
                                ->relationship(
                                    'project',
                                    'name',
                                    modifyQueryUsing: fn ($query) => $query->where('entrepreneur_id', auth()->user()?->entrepreneurProfile?->id)
                                )
                                ->required(),

                            Select::make('coach_id')
                                ->label('Coach / Mentor')
                                ->relationship('coach', 'name')
                                ->required(),

                            DateTimePicker::make('date')
                                ->label('Date & heure')
                                ->required(),

                            TextInput::make('duration')
                                ->label('Durée (min)')
                                ->numeric()
                                ->default(60)
                                ->suffix('min')
                                ->required(),

                            Select::make('type')
                                ->label('Type de séance')
                                ->options(CoachingSessionType::class)
                                ->default(CoachingSessionType::INDIVIDUEL->value)
                                ->required(),

                            Select::make('status')
                                ->label('Statut')
                                ->options(CoachingSessionStatus::class)
                                ->default(CoachingSessionStatus::PLANIFIEE->value)
                                ->disabled(),
                        ]),

                        TextInput::make('theme')
                            ->label('Thème de la séance')
                            ->placeholder('Ex: Revue du pitch deck & KPI')
                            ->columnSpanFull(),

                        Textarea::make('objectives')
                            ->label('Objectifs attendus')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Bilan & Recommandations (rempli par le coach)')
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        Textarea::make('report')
                            ->label('Compte-rendu du coach')
                            ->disabled()
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('next_actions')
                            ->label('Prochaines actions convenues')
                            ->disabled()
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
