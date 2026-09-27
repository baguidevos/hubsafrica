<?php

namespace App\Filament\Sae\Resources\TimelineEvents\Schemas;

use App\Models\Entrepreneur;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TimelineEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Événement de la chronologie')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet concerné')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur concerné')
                                ->options(function () {
                                    return Entrepreneur::all()->mapWithKeys(function ($e) {
                                        return [$e->id => "{$e->first_name} {$e->last_name}"];
                                    });
                                })
                                ->searchable()
                                ->preload(),

                            Select::make('type')
                                ->label('Type d\'événement')
                                ->options([
                                    'SESSION_COACHING' => 'Séance de coaching',
                                    'LIVRABLE_DEPOSE' => 'Dépôt de livrable',
                                    'LIVRABLE_VALIDE' => 'Validation de livrable',
                                    'FINANCEMENT' => 'Mobilisation de financement',
                                    'STATUT_PROJET' => 'Évolution du statut projet',
                                    'PALIER_FRANCHI' => 'Franchissement de palier CUBE',
                                    'APPLICATION' => 'Candidature & Sélection',
                                    'NOTE' => 'Note de suivi / Observation',
                                ])
                                ->required(),

                            TextInput::make('title')
                                ->label('Titre / Intitulé de l\'événement')
                                ->placeholder('Ex: BMC validé par le comité')
                                ->required(),

                            Textarea::make('description')
                                ->label('Description & Détails de l\'événement')
                                ->rows(3)
                                ->placeholder('Observations complémentaires...')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
