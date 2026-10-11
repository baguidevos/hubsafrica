<?php

namespace App\Filament\Admin\Resources\Saes\Schemas;

use App\Models\Sae;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SaeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Statut de la Structure')
                    ->description('Informations générales, typologies et responsable de la SAE')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo officiel de la structure')
                            ->image()
                            ->disk('public')
                            ->directory('saes/logos')
                            ->visibility('public')
                            ->imageEditor()
                            ->circleCropper()
                            ->helperText('Logo officiel qui apparaîtra sur les portails, les dossiers et les dashboards.')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom de la structure')
                                ->placeholder('Ex: Jokkolabs Dakar, 2iE Incubator...')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function ($state, callable $set, $operation) {
                                    if ($operation === 'create') {
                                        $set('slug', Str::slug($state));
                                    }
                                }),

                            TextInput::make('slug')
                                ->label('Identifiant unique (Slug URL)')
                                ->required()
                                ->maxLength(255)
                                ->unique(Sae::class, 'slug', ignoreRecord: true)
                                ->helperText('Identifiant dans l\'URL du portail et des espaces dédiés.'),

                            Select::make('owner_id')
                                ->label('Utilisateur Propriétaire / Responsable')
                                ->relationship('owner', 'name')
                                ->searchable()
                                ->preload()
                                ->placeholder('Sélectionner le compte responsable')
                                ->helperText('Compte utilisateur détenant l\'administration principale de cette SAE.'),

                            Select::make('status')
                                ->label('Statut opérationnel')
                                ->options([
                                    'ACTIVE' => 'Active (Opérationnelle)',
                                    'INACTIVE' => 'Inactive',
                                    'SUSPENDED' => 'Suspendue',
                                ])
                                ->default('ACTIVE')
                                ->required(),

                            Select::make('types')
                                ->label('Types de structure (SAE)')
                                ->relationship('types', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->columnSpanFull()
                                ->helperText('Sélectionnez les typologies correspondantes (ex: Couveuse, Espace coworking, Pépinière, Incubateur, Accélérateur...). Une SAE peut avoir plusieurs types.'),

                            Textarea::make('description')
                                ->label('Présentation synthétique & Pitch')
                                ->placeholder('Présentation en quelques phrases de la structure, de sa genèse et de son positionnement.')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Coordonnées & Contact officiel')
                    ->description('Canaux de communication institutionnels')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('email')
                                ->label('Email de contact officiel')
                                ->email()
                                ->maxLength(255)
                                ->placeholder('contact@incubateur.org'),

                            TextInput::make('phone')
                                ->label('Téléphone officiel / WhatsApp')
                                ->tel()
                                ->maxLength(50)
                                ->placeholder('+221 33 800 00 00'),

                            TextInput::make('website')
                                ->label('Site Web institutionnel')
                                ->url()
                                ->maxLength(255)
                                ->placeholder('https://www.incubateur.africa'),

                            Textarea::make('address')
                                ->label('Adresse physique / Ville / Pays')
                                ->rows(2)
                                ->placeholder('Ex: Point E, Boulevard de l\'Est, Dakar, Sénégal'),
                        ]),
                    ]),

                Section::make('Orientation Stratégique & Offre d\'accompagnement')
                    ->description('Vision, mission, piliers d\'impact et offre aux startups')
                    ->collapsible()
                    ->schema([
                        RichEditor::make('vision')
                            ->label('Vision stratégique à long terme')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),

                        RichEditor::make('mission')
                            ->label('Mission opérationnelle')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),

                        RichEditor::make('objectives')
                            ->label('Objectifs prioritaires & Piliers d\'impact')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),

                        RichEditor::make('services')
                            ->label('Services & Offre d\'accompagnement détaillée')
                            ->toolbarButtons([
                                'bold', 'italic', 'underline', 'bulletList', 'orderedList', 'link', 'h2', 'h3', 'undo', 'redo',
                            ])
                            ->columnSpanFull(),
                    ]),

                Section::make('Paramètres de Délibération & Seuils Jury')
                    ->description('Configuration des seuils minimaux pour les jurys d\'admission')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('default_admission_threshold')
                                ->label('Seuil d\'admission directe par défaut (/100)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(75)
                                ->helperText('Note minimale requise par défaut pour qu\'un dossier soit directement admis.'),

                            TextInput::make('default_waitlist_threshold')
                                ->label('Seuil de liste d\'attente par défaut (/100)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default(50)
                                ->helperText('Note minimale par défaut pour être placé en liste d\'attente.'),
                        ]),
                    ]),
            ]);
    }
}
