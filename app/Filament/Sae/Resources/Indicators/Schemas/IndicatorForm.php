<?php

namespace App\Filament\Sae\Resources\Indicators\Schemas;

use App\Enums\IndicatorCategory;
use App\Enums\IndicatorStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndicatorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Définition de l\'indicateur')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom de l\'indicateur')
                                ->placeholder('Ex: Taux de survie à 12 mois')
                                ->required(),

                            Select::make('category')
                                ->label('Catégorie')
                                ->options(IndicatorCategory::class)
                                ->default(IndicatorCategory::PORTEE)
                                ->required(),

                            Select::make('unit')
                                ->label('Unité de mesure')
                                ->options([
                                    '%' => 'Pourcentage (%)',
                                    'FCFA' => 'Montant financier (FCFA)',
                                    'NOMBRE' => 'Nombre entier / Effectif',
                                    'JOURS' => 'Durée en jours',
                                    'HEURES' => 'Heures de formation / coaching',
                                ])
                                ->default('%')
                                ->required(),

                            Select::make('frequency')
                                ->label('Fréquence de mesure')
                                ->options([
                                    'MENSUEL' => 'Mensuel',
                                    'TRIMESTRIEL' => 'Trimestriel',
                                    'SEMESTRIEL' => 'Semestriel',
                                    'ANNUEL' => 'Annuel',
                                ])
                                ->default('TRIMESTRIEL')
                                ->required(),

                            TextInput::make('source')
                                ->label('Source de données')
                                ->placeholder('Ex: Registre SAE, Déclarations comptables, Feuilles d\'émargement'),

                            TextInput::make('responsible')
                                ->label('Responsable du suivi')
                                ->placeholder('Ex: Responsable S&E, Coach référent'),

                            Textarea::make('definition')
                                ->label('Définition méthodologique')
                                ->placeholder('Description précise de l\'indicateur...')
                                ->rows(2)
                                ->columnSpanFull(),

                            Textarea::make('formula')
                                ->label('Formule de calcul')
                                ->placeholder('Ex: (Entreprises actives à M+12 / Entreprises diplômées) * 100')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Valeurs & Performance')
                    ->schema([
                        Grid::make(4)->schema([
                            TextInput::make('target')
                                ->label('Cible visée')
                                ->numeric(),

                            TextInput::make('current')
                                ->label('Valeur actuelle')
                                ->numeric(),

                            TextInput::make('previous')
                                ->label('Valeur précédente')
                                ->numeric(),

                            Select::make('status')
                                ->label('Statut de performance')
                                ->options(IndicatorStatus::class)
                                ->default(IndicatorStatus::EN_BONNE_VOIE)
                                ->required(),
                        ]),

                        Textarea::make('comment')
                            ->label('Analyse & Plan d\'action correctif')
                            ->rows(2)
                            ->placeholder('Explications des écarts constatés par rapport à la cible...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
