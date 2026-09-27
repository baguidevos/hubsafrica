<?php

namespace App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Schemas;

use App\Enums\DeliverableSubmissionStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeliverableSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('entrepreneur_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->id),

                Hidden::make('sae_id')
                    ->default(fn () => auth()->user()?->entrepreneurProfile?->sae_id),

                Hidden::make('submitted_by_id')
                    ->default(fn () => auth()->id()),

                Hidden::make('status')
                    ->default(DeliverableSubmissionStatus::SOUMIS),

                Hidden::make('submitted_at')
                    ->default(now()),

                Section::make('Dépôt de votre livrable')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('project_id')
                                ->label('Projet concerné')
                                ->relationship('project', 'name')
                                ->required(),

                            Select::make('definition_id')
                                ->label('Livrable attendu')
                                ->relationship('definition', 'name')
                                ->required()
                                ->searchable()
                                ->preload(),

                            TextInput::make('version')
                                ->label('Version')
                                ->numeric()
                                ->default(1)
                                ->prefix('v')
                                ->required(),

                            TextInput::make('file_name')
                                ->label('Titre du document')
                                ->placeholder('Ex: BMC_version_finale.pdf')
                                ->required(),
                        ]),

                        FileUpload::make('file_path')
                            ->label('Fichier du livrable (PDF, Word, Excel, Slide...)')
                            ->disk('public')
                            ->directory('deliverables')
                            ->openable()
                            ->downloadable()
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('comment')
                            ->label('Message / Commentaires pour votre coach')
                            ->rows(3)
                            ->placeholder('Précisez les avancées notables ou les questions spécifiques sur ce livrable...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
