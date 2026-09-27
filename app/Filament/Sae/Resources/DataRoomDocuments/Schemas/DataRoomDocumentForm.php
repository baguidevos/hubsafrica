<?php

namespace App\Filament\Sae\Resources\DataRoomDocuments\Schemas;

use App\Models\Entrepreneur;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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
                Section::make('Document & Rattachement')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('entrepreneur_id')
                                ->label('Entrepreneur propriétaire')
                                ->options(function () {
                                    return Entrepreneur::all()->mapWithKeys(function ($e) {
                                        return [$e->id => "{$e->first_name} {$e->last_name}"];
                                    });
                                })
                                ->searchable()
                                ->preload()
                                ->required(),

                            Select::make('project_id')
                                ->label('Projet associé (optionnel)')
                                ->relationship('project', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('name')
                                ->label('Titre du document')
                                ->placeholder('Ex: Registre du Commerce (RCCM), Bilan 2025')
                                ->required(),

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
                        ]),
                    ]),

                Section::make('Fichier & Notes')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('Fichier du document (PDF, Image, Archive...)')
                            ->disk('public')
                            ->directory('dataroom')
                            ->openable()
                            ->downloadable()
                            ->columnSpanFull(),

                        TextInput::make('file_name')
                            ->label('Nom d\'origine du fichier')
                            ->placeholder('Ex: RCCM_Ecobat_2025.pdf')
                            ->columnSpanFull(),

                        Textarea::make('comment')
                            ->label('Notes & Description')
                            ->rows(2)
                            ->placeholder('Précisions sur la validité ou la version de la pièce...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Contrôle & Validation SAE')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('status')
                                ->label('Statut de conformité')
                                ->options([
                                    'RECU' => 'Reçu / En attente de revue',
                                    'VALIDE' => 'Validé & Conforme',
                                    'REJETE' => 'Rejeté / Pièce non conforme',
                                ])
                                ->default('RECU')
                                ->required(),

                            DateTimePicker::make('reviewed_at')
                                ->label('Date du contrôle'),
                        ]),
                    ]),
            ]);
    }
}
