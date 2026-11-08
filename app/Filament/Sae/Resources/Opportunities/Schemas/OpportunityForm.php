<?php

namespace App\Filament\Sae\Resources\Opportunities\Schemas;

use App\Services\WebpageMetadataScraperService;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
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
                        FileUpload::make('image')
                            ->label('Visuel / Image d\'illustration de l\'opportunité')
                            ->image()
                            ->disk('public')
                            ->directory('opportunities/covers')
                            ->visibility('public')
                            ->imageEditor()
                            ->helperText('Image d\'illustration (JPEG, PNG, WebP — max 5 Mo). Astuce : collez un lien ci-dessous et cliquez sur 🪄 pour récupérer le visuel automatiquement.')
                            ->maxSize(5120)
                            ->columnSpanFull(),

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
                                ->helperText('Cliquez sur 🪄 pour extraire le visuel et les informations officielles depuis cette page.')
                                ->suffixAction(static::createMetadataExtractAction()),

                            TextInput::make('apply_url')
                                ->label('Lien pour postuler')
                                ->url()
                                ->placeholder('https://opportunite.org/candidater')
                                ->helperText('Lien direct de soumission. Cliquez sur 🪄 pour extraire les métadonnées si besoin.')
                                ->suffixAction(static::createMetadataExtractAction()),
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

    /**
     * Action réutilisable d'extraction des métadonnées Open Graph (visuel, titre, description).
     */
    private static function createMetadataExtractAction(): Action
    {
        return Action::make('extractMetadata')
            ->label('Extraire visuel')
            ->icon('heroicon-m-sparkles')
            ->color('amber')
            ->tooltip('Récupérer automatiquement le visuel et les informations de la page web')
            ->action(function ($state, $set, $get, WebpageMetadataScraperService $scraper): void {
                $targetUrl = trim((string) $state);

                if (empty($targetUrl)) {
                    Notification::make()
                        ->title('Lien requis')
                        ->warning()
                        ->body('Veuillez d\'abord saisir une adresse URL valide dans le champ.')
                        ->send();

                    return;
                }

                try {
                    $metadata = $scraper->scrapeFromUrl($targetUrl);

                    $hasExtractedAnything = false;

                    if ($metadata->hasImage()) {
                        $storedImagePath = $scraper->downloadAndStoreCoverImage($metadata->imageUrl);
                        if ($storedImagePath) {
                            $set('image', $storedImagePath);
                            $hasExtractedAnything = true;
                        }
                    }

                    if ($metadata->hasTitle() && empty($get('title'))) {
                        $set('title', $metadata->title);
                        $hasExtractedAnything = true;
                    }

                    if ($metadata->hasDescription() && empty($get('details'))) {
                        $set('details', '<p>'.e($metadata->description).'</p>');
                        $hasExtractedAnything = true;
                    }

                    if ($hasExtractedAnything) {
                        Notification::make()
                            ->title('Métadonnées importées avec succès !')
                            ->success()
                            ->body('Le visuel officiel et les métadonnées ont été appliqués au formulaire.')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Aucun visuel détecté')
                        ->warning()
                        ->body('La page a été consultée mais ne contient pas de balises Open Graph (og:image) exploitables.')
                        ->send();
                } catch (\Throwable $exception) {
                    Notification::make()
                        ->title('Impossible d\'extraire les métadonnées')
                        ->danger()
                        ->body($exception->getMessage())
                        ->send();
                }
            });
    }
}
