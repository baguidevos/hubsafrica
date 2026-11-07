<?php

namespace App\Filament\Sae\Resources\Opportunities\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OpportunityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->description('Renseignez les éléments essentiels de l\'opportunité ou de l\'appel à projets.')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nom de l\'opportunité')
                            ->placeholder('Ex: Challenge AgriTech & Climat Sahel 2026')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TagsInput::make('sectors')
                                ->label('Secteurs concernés')
                                ->placeholder('Ajouter un secteur...')
                                ->suggestions([
                                    'AgriTech',
                                    'FinTech',
                                    'EdTech',
                                    'Santé & MedTech',
                                    'Énergie & Climat',
                                    'E-commerce & Logistique',
                                    'Industrie & Transformation',
                                    'Artisanat & Métiers',
                                    'Services B2B',
                                    'Industries Créatives & Culture',
                                    'Tous secteurs',
                                ])
                                ->helperText('Appuyez sur Entrée pour valider chaque secteur.'),

                            TagsInput::make('target_zones')
                                ->label('Zones géographiques concernées')
                                ->placeholder('Ajouter une zone...')
                                ->suggestions([
                                    'Panafricain',
                                    'Afrique de l\'Ouest',
                                    'Afrique Centrale',
                                    'Afrique du Nord',
                                    'Afrique de l\'Est',
                                    'UEMOA',
                                    'CEDEAO',
                                    'CEMAC',
                                    'Sénégal',
                                    'Côte d\'Ivoire',
                                    'Togo',
                                    'Bénin',
                                    'Cameroun',
                                    'Mali',
                                    'Burkina Faso',
                                    'Guinée',
                                ])
                                ->helperText('Ex: Panafricain, Sénégal, UEMOA...'),
                        ]),
                    ]),

                Section::make('Détails & Description complète')
                    ->description('Présentation détaillée de l\'opportunité, critères d\'éligibilité, dotations et calendrier.')
                    ->schema([
                        RichEditor::make('details')
                            ->label('Détails de l\'opportunité')
                            ->placeholder('Présentez les critères d\'éligibilité, le profil des startups recherchées, les dotations (financements, mentorat), les étapes de sélection...')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Liens d\'action & Clôture')
                    ->description('Renseignez les liens externes et l\'échéance des candidatures.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('learn_more_url')
                                ->label('Lien pour en savoir plus')
                                ->url()
                                ->placeholder('https://opportunite.org/details')
                                ->helperText('Page officielle, règlement ou documentation complète.'),

                            TextInput::make('apply_url')
                                ->label('Lien pour postuler')
                                ->url()
                                ->placeholder('https://opportunite.org/candidater')
                                ->helperText('Lien direct vers la plateforme de soumission.'),
                        ]),

                        Grid::make(2)->schema([
                            DateTimePicker::make('deadline')
                                ->label('Date et heure limite (Deadline)')
                                ->native(false)
                                ->seconds(false)
                                ->placeholder('JJ/MM/AAAA HH:MM')
                                ->helperText('Une fois la date dépassée, l\'opportunité sera signalée en rouge (Clôturée) sur la plateforme.'),

                            Toggle::make('is_active')
                                ->label('Publier directement sur la plateforme')
                                ->default(true)
                                ->inline(false)
                                ->helperText('Actif = visible immédiatement sur le catalogue public d\'opportunités.'),
                        ]),
                    ]),
            ]);
    }
}
