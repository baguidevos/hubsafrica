<?php

namespace App\Filament\Sae\Resources\Resources\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ResourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ressource pédagogique & Outils')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Titre de la ressource')
                                ->placeholder('Ex: Canevas Business Plan, Grille d\'évaluation pitch, Guide juridique')
                                ->required()
                                ->columnSpanFull(),

                            Select::make('type')
                                ->label('Format de ressource')
                                ->options([
                                    'DOCUMENT' => 'Document à télécharger (PDF, Excel, Word...)',
                                    'VIDEO' => 'Vidéo pédagogique',
                                    'LIEN_EXTERNE' => 'Lien / Outil en ligne',
                                ])
                                ->default('DOCUMENT')
                                ->reactive()
                                ->required(),

                            Select::make('visibility')
                                ->label('Visibilité & Accès')
                                ->options([
                                    'TOUS' => 'Tous les membres (Public SAE)',
                                    'ENTREPRENEUR_ONLY' => 'Entrepreneurs uniquement',
                                    'COACH_ONLY' => 'Coaches uniquement',
                                    'INTERNAL' => 'Équipe SAE uniquement',
                                ])
                                ->default('TOUS')
                                ->required(),

                            Select::make('level_id')
                                ->label('Niveau CUBE associé')
                                ->relationship('level', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('category')
                                ->label('Thématique / Catégorie')
                                ->placeholder('Ex: Finance, Marketing, Juridique, Pitch'),

                            TextInput::make('author')
                                ->label('Auteur / Source')
                                ->placeholder('Ex: Teranga Hub, Bpifrance, CUBE'),

                            TextInput::make('url')
                                ->label('Lien URL (Web ou Vidéo)')
                                ->url()
                                ->placeholder('https://...')
                                ->columnSpanFull(),

                            FileUpload::make('file_path')
                                ->label('Fichier du document joint')
                                ->disk('public')
                                ->directory('pedatheque')
                                ->openable()
                                ->downloadable()
                                ->columnSpanFull(),

                            Textarea::make('description')
                                ->label('Description & Conseils d\'utilisation')
                                ->rows(3)
                                ->placeholder('Précisez comment exploiter au mieux ce modèle ou outil...')
                                ->columnSpanFull(),
                        ]),
                    ]),
            ]);
    }
}
