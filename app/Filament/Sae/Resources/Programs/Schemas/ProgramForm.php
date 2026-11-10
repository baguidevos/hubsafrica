<?php

namespace App\Filament\Sae\Resources\Programs\Schemas;

use App\Enums\ProgramStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Présentation du programme')
                    ->schema([
                        Grid::make(2)->schema([
                            FileUpload::make('image')
                                ->label('Visuel / Image de couverture du programme')
                                ->image()
                                ->disk('public')
                                ->directory('programs/covers')
                                ->visibility('public')
                                ->imageEditor()
                                ->helperText('Image d\'illustration (JPEG, PNG, WebP — max 5 Mo) affichée sur le portail public et la fiche du programme.')
                                ->maxSize(5120)
                                ->columnSpanFull(),

                            TextInput::make('name')
                                ->label('Nom du programme')
                                ->placeholder('Ex: Accélération Impact 2026')
                                ->required(),

                            Select::make('level_id')
                                ->label('Niveau ciblé')
                                ->relationship('level', 'name')
                                ->searchable()
                                ->preload(),

                            TextInput::make('target')
                                ->label('Public / Profil cible')
                                ->placeholder('Ex: Startups Seed / Early-stage, AgriTech, GreenTech'),

                            TextInput::make('capacity')
                                ->label('Capacité totale d\'accueil')
                                ->numeric()
                                ->suffix('startups'),

                            Select::make('status')
                                ->label('Statut du programme')
                                ->options(ProgramStatus::class)
                                ->default(ProgramStatus::OUVERT_CANDIDATURES)
                                ->required(),

                            Toggle::make('is_active')
                                ->label('Programme actif (visible sur le portail public)')
                                ->helperText('Désactivez pour masquer temporairement ce programme sur le site public sans le supprimer.')
                                ->default(true)
                                ->inline(false),

                            Textarea::make('description')
                                ->label('Description générale')
                                ->placeholder('Présentation détaillée du programme...')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('objectives')
                                ->label('Objectifs & Livrables visés')
                                ->placeholder('Objectifs de croissance, levée de fonds, structuration...')
                                ->rows(3)
                                ->columnSpanFull(),

                            Textarea::make('eligibility')
                                ->label('Critères d\'éligibilité')
                                ->placeholder('Conditions à remplir par les entrepreneurs candidats...')
                                ->rows(3)
                                ->columnSpanFull(),
                        ]),
                    ]),

                Section::make('Calendrier & Candidatures')
                    ->schema([
                        Grid::make(2)->schema([
                            DatePicker::make('registration_start')
                                ->label('Ouverture des candidatures'),

                            DatePicker::make('registration_end')
                                ->label('Clôture des candidatures'),

                            DatePicker::make('start_date')
                                ->label('Date de lancement effectif'),

                            DatePicker::make('end_date')
                                ->label('Date de clôture prévisionnelle'),
                        ]),
                    ]),

                Section::make('Seuils de Délibération & Admission Jury')
                    ->description('Personnalisez la sélectivité pour ce programme. Laissez vide pour utiliser les seuils par défaut de votre structure SAE.')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('admission_threshold')
                                ->label('Seuil d\'admission directe (/100)')
                                ->placeholder('Défaut SAE (ex: 75)')
                                ->helperText('Note minimale requise pour que le jury admette automatiquement le projet dans la cohorte.')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100),

                            TextInput::make('waitlist_threshold')
                                ->label('Seuil de liste d\'attente (/100)')
                                ->placeholder('Défaut SAE (ex: 50)')
                                ->helperText('Note minimale requise pour la liste d\'attente. En-dessous, le dossier est refusé.')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100),
                        ]),
                    ]),
            ]);
    }
}
