<?php

namespace App\Filament\Sae\Resources\CourseModules\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Leçons du module';

    protected static ?string $modelLabel = 'Leçon';

    protected static ?string $pluralModelLabel = 'Leçons';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations générales')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')
                            ->label('Titre de la leçon')
                            ->placeholder('Ex: Construire sa proposition de valeur')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Résumé court')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Vidéo & Supports pédagogiques')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('video_url')
                                    ->label('Lien vidéo (YouTube ou Vimeo)')
                                    ->url()
                                    ->placeholder('https://www.youtube.com/watch?v=... ou https://vimeo.com/...'),

                                TextInput::make('duration_in_seconds')
                                    ->label('Durée (en secondes)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('Ex: 420 pour 7 min'),

                                FileUpload::make('video_path')
                                    ->label('Ou fichier vidéo uploadé (MP4 / WebM)')
                                    ->disk('public')
                                    ->directory('courses/videos')
                                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                                    ->maxSize(102400),

                                FileUpload::make('attachment_path')
                                    ->label('Support PDF / Document joint (Optionnel)')
                                    ->disk('public')
                                    ->directory('courses/attachments')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                    ])
                                    ->maxSize(20480),
                            ]),
                    ]),

                Section::make('Contenu & Notes pédagogiques')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Notes de cours / Exercices')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publication & Paramètres')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('sort_order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Toggle::make('is_free')
                                    ->label('Aperçu libre (tous paliers)')
                                    ->inline(false)
                                    ->default(false),

                                Toggle::make('is_published')
                                    ->label('Publiée')
                                    ->inline(false)
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Leçon')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('duration_in_seconds')
                    ->label('Durée')
                    ->formatStateUsing(fn ($record) => $record->getFormattedDuration() ?? '—'),

                IconColumn::make('is_free')
                    ->label('Aperçu')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Publiée')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une leçon')
                    ->modalWidth(Width::FiveExtraLarge),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::FiveExtraLarge),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
