<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('entrepreneur_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->id),

                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->sae_id),

                Section::make('Identité de votre projet d\'entreprise')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du projet / Startup')
                                ->placeholder('Ex: Ecobat Green')
                                ->required(),

                            Select::make('sector')
                                ->label('Secteur d\'activité')
                                ->options([
                                    'Agriculture' => 'Agriculture & Agroalimentaire',
                                    'Fintech' => 'Fintech & Services financiers',
                                    'Santé' => 'Santé & Biotech',
                                    'Énergie' => 'Énergie verte & Climat',
                                    'Éducation' => 'Éducation & EdTech',
                                    'Artisanat' => 'Artisanat & Création',
                                    'Commerce' => 'Commerce & Distribution',
                                    'Autre' => 'Autre secteur',
                                ])
                                ->required(),

                            TextInput::make('territory')
                                ->label('Ville / Région d\'implantation')
                                ->placeholder('Ex: Dakar'),

                            TextInput::make('team_size')
                                ->label('Taille de l\'équipe')
                                ->numeric()
                                ->default(1)
                                ->suffix('personnes'),

                            FileUpload::make('logo')
                                ->label('Logo de l\'entreprise')
                                ->image()
                                ->disk('public')
                                ->directory('projects/logos')
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Offre & Proposition de valeur')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('target')
                                ->label('Cible de clientèle (B2B, B2C, B2G)'),

                            TextInput::make('market')
                                ->label('Marché visé'),
                        ]),

                        Textarea::make('value_proposition')
                            ->label('Proposition de valeur unique')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('problem')
                            ->label('Problème client identifié')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('solution')
                            ->label('Solution proposée')
                            ->rows(2)
                            ->columnSpanFull(),

                        Textarea::make('product_service')
                            ->label('Produits & Services')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pitch & Présentation')
                    ->schema([
                        Textarea::make('pitch')
                            ->label('Pitch Elevator (en quelques phrases)')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Historique & Présentation générale')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
