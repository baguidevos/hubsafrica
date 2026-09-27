<?php

namespace App\Filament\Sae\Resources\Projects\Schemas;

use App\Enums\ProjectStatus;
use App\Models\Entrepreneur;
use Filament\Forms\Components\FileUpload;
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
                Section::make('Identité & Positionnement du Projet')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom du projet / Startup')
                                ->placeholder('Ex: Ecobat Green Solutions')
                                ->required(),

                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur porteur')
                                ->options(function () {
                                    return Entrepreneur::all()->mapWithKeys(function ($e) {
                                        return [$e->id => "{$e->first_name} {$e->last_name}"];
                                    });
                                })
                                ->searchable()
                                ->preload()
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
                                ->searchable()
                                ->required(),

                            TextInput::make('territory')
                                ->label('Territoire / Ville d\'implantation')
                                ->placeholder('Ex: Dakar, Thiès, Ziguinchor'),

                            Select::make('current_level_id')
                                ->label('Palier actuel')
                                ->relationship('currentLevel', 'name')
                                ->searchable()
                                ->preload(),

                            Select::make('status')
                                ->label('Statut d\'accompagnement')
                                ->options(ProjectStatus::class)
                                ->default(ProjectStatus::ACTIF)
                                ->required(),

                            TextInput::make('team_size')
                                ->label('Taille de l\'équipe')
                                ->numeric()
                                ->default(1)
                                ->suffix('personnes'),

                            FileUpload::make('logo')
                                ->label('Logo de l\'entreprise')
                                ->image()
                                ->disk('public')
                                ->directory('projects/logos'),
                        ]),
                    ]),

                Section::make('Modèle économique & Proposition de valeur')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('target')
                                ->label('Cible de clientèle (B2B, B2C, B2G)')
                                ->placeholder('Ex: PME agroalimentaires régionales'),

                            TextInput::make('market')
                                ->label('Marché visé')
                                ->placeholder('Ex: Marché national & Zone UEMOA'),
                        ]),

                        Textarea::make('value_proposition')
                            ->label('Proposition de valeur')
                            ->rows(2)
                            ->placeholder('Quel avantage concurrentiel unique apporte le projet ?')
                            ->columnSpanFull(),

                        Textarea::make('problem')
                            ->label('Problème client identifié')
                            ->rows(2)
                            ->placeholder('Pain points et besoins non satisfaits sur le marché...')
                            ->columnSpanFull(),

                        Textarea::make('solution')
                            ->label('Solution apportée')
                            ->rows(2)
                            ->placeholder('Comment le produit/service résout ce problème...')
                            ->columnSpanFull(),

                        Textarea::make('product_service')
                            ->label('Offre de Produits & Services')
                            ->rows(2)
                            ->placeholder('Détail du catalogue produits/services...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pitch Elevator & Description')
                    ->schema([
                        Textarea::make('pitch')
                            ->label('Pitch Elevator')
                            ->rows(3)
                            ->placeholder('Accroche percutante résumant la vision et l\'ambition...')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description générale détaillée')
                            ->rows(3)
                            ->placeholder('Historique, vision à long terme...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
