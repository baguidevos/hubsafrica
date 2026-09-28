<?php

namespace App\Filament\Entrepreneur\Resources\DataRoomDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DataRoomDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('entrepreneur_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->id),

                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->sae_id),

                Hidden::make('status')
                    ->default('RECU'),

                Section::make('Dépôt de pièce / Justificatif')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet associé (optionnel)')
                                ->relationship(
                                    'project',
                                    'name',
                                    modifyQueryUsing: fn ($query) => $query->where('entrepreneur_id', auth()->user()?->entrepreneurProfile?->id)
                                )
                                ->searchable()
                                ->preload(),

                            Select::make('category')
                                ->label('Catégorie Data Room')
                                ->options([
                                    'ADMINISTRATIF' => 'Administratif & Identité',
                                    'JURIDIQUE' => 'Juridique & Statuts',
                                    'FINANCE' => 'Finance & Comptabilité',
                                    'COMMERCIAL' => 'Commercial & Contrats',
                                    'TECHNIQUE' => 'Technique & Propriété intellectuelle',
                                    'RH' => 'Ressources humaines',
                                    'AUTRE' => 'Autre pièce',
                                ])
                                ->default('ADMINISTRATIF')
                                ->required(),

                            TextInput::make('name')
                                ->label('Titre du document')
                                ->placeholder('Ex: Registre du Commerce (RCCM), Bilan 2025, Statuts signés')
                                ->required()
                                ->columnSpanFull(),
                        ]),

                        FileUpload::make('file_path')
                            ->label('Fichier du document (PDF, Image, Archive...)')
                            ->disk('public')
                            ->directory('dataroom')
                            ->openable()
                            ->downloadable()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('file_name')
                            ->label('Nom d\'origine du fichier (optionnel)')
                            ->placeholder('Ex: RCCM_Ecobat_2025.pdf')
                            ->columnSpanFull(),

                        Textarea::make('comment')
                            ->label('Notes & Description')
                            ->rows(2)
                            ->placeholder('Précisions utiles sur la pièce déposée...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Statut de vérification SAE')
                    ->visible(fn ($record) => $record !== null)
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('statut_display')
                                ->label('Statut de conformité')
                                ->default(fn ($record) => match ($record?->status) {
                                    'VALIDE' => 'Validé & Conforme',
                                    'REJETE' => 'Rejeté / Pièce non conforme',
                                    default => 'Reçu / En cours d\'examen',
                                })
                                ->disabled(),

                            TextInput::make('reviewed_at_display')
                                ->label('Date de vérification')
                                ->default(fn ($record) => $record?->reviewed_at?->format('d/m/Y H:i') ?? 'Non examiné')
                                ->disabled(),
                        ]),
                    ]),
            ]);
    }
}
