<?php

namespace App\Filament\Sae\Resources\AccompanimentTools\Schemas;

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolFormat;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccompanimentToolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->deferLoading()
            ->components([
                Hidden::make('created_by_id')
                    ->default(fn () => auth()->id()),

                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Titre de l\'outil')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('category')
                                ->label('Catégorie (optionnel)')
                                ->placeholder('Ex: Finance, Modèle économique, Juridique')
                                ->maxLength(100),

                            Select::make('saeTypes')
                                ->label('Types de SAE cibles')
                                ->relationship('saeTypes', 'name', fn ($query) => $query->where('is_active', true))
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->helperText('Ex: Couveuse, Préincubation, Incubateur... Laissez vide pour tous les types de structures.')
                                ->columnSpanFull(),
                        ]),

                        Textarea::make('description')
                            ->label('Description courte')
                            ->placeholder('Ex : Référentiel pratique pour structurer le parcours d\'accompagnement selon le type de SAE.')
                            ->helperText('Affichée sur la carte de l\'outil dans le catalogue public et la bibliothèque réseau (2 à 3 lignes recommandées).')
                            ->required()
                            ->maxLength(500)
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Support & Accès')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('format')
                                ->label('Format de l\'outil')
                                ->options(AccompanimentToolFormat::class)
                                ->default(AccompanimentToolFormat::DOCUMENT)
                                ->required()
                                ->live(),

                            Select::make('access_level')
                                ->label('Niveau d\'accès')
                                ->options(AccompanimentToolAccessLevel::class)
                                ->default(AccompanimentToolAccessLevel::FREE)
                                ->required()
                                ->live(),
                        ]),

                        FileUpload::make('file_path')
                            ->label('Document à partager (stockage sécurisé)')
                            ->disk('local')
                            ->directory('accompaniment-tools')
                            ->visibility('private')
                            ->storeFileNamesIn('original_filename')
                            ->visible(fn ($get): bool => self::enumValue($get('format')) === AccompanimentToolFormat::DOCUMENT->value)
                            ->required(fn ($get): bool => self::enumValue($get('format')) === AccompanimentToolFormat::DOCUMENT->value),

                        TextInput::make('drive_url')
                            ->label('Lien Google Drive / Cloud')
                            ->url()
                            ->placeholder('https://drive.google.com/...')
                            ->visible(fn ($get): bool => self::enumValue($get('format')) === AccompanimentToolFormat::DRIVE_LINK->value)
                            ->required(fn ($get): bool => self::enumValue($get('format')) === AccompanimentToolFormat::DRIVE_LINK->value),

                        Grid::make(2)
                            ->visible(fn ($get): bool => self::enumValue($get('access_level')) === AccompanimentToolAccessLevel::PAID->value)
                            ->schema([
                                TextInput::make('price')
                                    ->label('Prix d\'accès')
                                    ->numeric()
                                    ->minValue(100)
                                    ->required(fn ($get): bool => self::enumValue($get('access_level')) === AccompanimentToolAccessLevel::PAID->value),

                                Select::make('currency')
                                    ->label('Devise')
                                    ->options([
                                        'XOF' => 'XOF (FCFA BCEAO)',
                                        'XAF' => 'XAF (FCFA BEAC)',
                                        'EUR' => 'EUR (€)',
                                        'USD' => 'USD ($)',
                                    ])
                                    ->default('XOF')
                                    ->required(),
                            ]),

                        Toggle::make('is_published')
                            ->label('Publier dans l\'onglet public « Outils »')
                            ->default(true),

                        Toggle::make('is_shared_with_network')
                            ->label('Partager avec les autres SAEI & Coachs inscrits')
                            ->helperText('Rend cet outil directement visible dans la bibliothèque réseau des SAEI et Coachs correspondant aux types sélectionnés.')
                            ->default(false),
                    ]),
            ]);
    }

    private static function enumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        return is_string($value) ? $value : null;
    }
}
