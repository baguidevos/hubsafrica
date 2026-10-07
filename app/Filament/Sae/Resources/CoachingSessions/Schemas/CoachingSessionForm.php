<?php

namespace App\Filament\Sae\Resources\CoachingSessions\Schemas;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use App\Enums\SessionPeriodOfDay;
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
                    ->description('Définissez le format, les intervenants et le rattachement de la séance d\'accompagnement.')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('coach_id')
                                ->label('Coach / Expert intervenant')
                                ->relationship('coach', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('type')
                                ->label('Format de la séance')
                                ->options(CoachingSessionType::class)
                                ->default(CoachingSessionType::INDIVIDUEL)
                                ->required(),

                            Select::make('status')
                                ->label('Statut de la séance')
                                ->options(CoachingSessionStatus::class)
                                ->default(CoachingSessionStatus::PLANIFIEE)
                                ->required(),

                            Select::make('project_id')
                                ->label('Startup concernée (Si session individuelle)')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Pour un coaching 1-to-1 ciblé sur une startup'),

                            Select::make('cohort_id')
                                ->label('Cohorte ciblée (Si atelier collectif)')
                                ->relationship('cohort', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Pour les masterclasses, boot camps et ateliers de promotion'),

                            Select::make('cohort_phase_id')
                                ->label('Jalon pédagogique associé')
                                ->relationship('cohortPhase', 'name')
                                ->searchable()
                                ->preload()
                                ->helperText('Phase du programme au cours de laquelle s\'inscrit l\'atelier'),

                            TextInput::make('session_number')
                                ->label('Séance N°')
                                ->numeric()
                                ->placeholder('Ex: 1 à 8')
                                ->helperText('Numéro séquentiel dans le cycle de coaching (C1 / C2)'),

                            Select::make('period_of_day')
                                ->label('Créneau horaire')
                                ->options(SessionPeriodOfDay::class)
                                ->placeholder('Sélectionner le créneau'),

                            DateTimePicker::make('date')
                                ->label('Date & heure planifiée')
                                ->required()
                                ->default(now()),

                            TextInput::make('duration')
                                ->label('Durée prévue')
                                ->numeric()
                                ->suffix('minutes')
                                ->default(60)
                                ->required(),

                            TextInput::make('hours_delivered')
                                ->label('Heures délivrées')
                                ->numeric()
                                ->step('0.25')
                                ->suffix('h')
                                ->placeholder('Ex: 1.5')
                                ->helperText('Volume horaire effectif pour le décompte M&E'),

                            TextInput::make('theme')
                                ->label('Thématique / Intitulé de l\'atelier')
                                ->placeholder('Ex: Modèle économique, Business Plan, Pitch, Levée de fonds')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Contenu pédagogique & Compte-rendu')
                    ->description('Consignez les objectifs, les échanges et les prochaines étapes opérationnelles.')
                    ->schema([
                        Textarea::make('objectives')
                            ->label('Objectifs & Compétences visées')
                            ->rows(2)
                            ->placeholder('Quels étaient les livrables ou compétences visés ?')
                            ->columnSpanFull(),

                        Textarea::make('report')
                            ->label('Compte-rendu des échanges & Recommandations')
                            ->rows(4)
                            ->placeholder('Synthèse des discussions, difficultés remontées et conseils donnés...')
                            ->columnSpanFull(),

                        Textarea::make('next_actions')
                            ->label('Prochaines actions convenues (Roadmap & Livrables)')
                            ->rows(2)
                            ->placeholder('Engagements et livrables attendus pour la prochaine session...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
