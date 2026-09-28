<?php

namespace App\Filament\Sae\Resources\CoachingSessions\RelationManagers;

use App\Enums\DocumentCategory;
use App\Services\AttendanceService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Feuilles signées & Livrables (GED)';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-paper-clip';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Titre du document')
                    ->weight('bold')
                    ->default(fn ($record) => $record->original_name)
                    ->searchable(),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge(),

                TextColumn::make('original_name')
                    ->label('Fichier joint')
                    ->description(fn ($record) => $record->human_size),

                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => "v{$state}"),

                TextColumn::make('uploader.name')
                    ->label('Déposé par')
                    ->default('Système'),

                TextColumn::make('created_at')
                    ->label('Date d\'archivage')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('addDocument')
                    ->label('Joindre un document / Feuille signée')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->modalHeading('Déposer une feuille d\'émargement ou un livrable')
                    ->modalDescription('Le fichier sera conservé dans la GED avec horodatage et versionnage automatique.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Fichier (PDF, Excel, Scan image)')
                            ->disk('public')
                            ->directory('coaching-sessions/documents')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'image/jpeg',
                                'image/png',
                            ])
                            ->maxSize(15360)
                            ->required(),

                        TextInput::make('title')
                            ->label('Titre du document')
                            ->default(fn () => 'Feuille d\'émargement signée - '.($this->getOwnerRecord()->theme ?? 'Séance du '.now()->format('d/m/Y')))
                            ->required(),

                        Select::make('category')
                            ->label('Classification GED')
                            ->options(DocumentCategory::class)
                            ->default(DocumentCategory::LIVRABLE->value)
                            ->required(),
                    ])
                    ->action(function (array $data, AttendanceService $service) {
                        $session = $this->getOwnerRecord();
                        $storedPath = $data['file'];
                        $originalName = basename($storedPath);

                        $service->attachSignedDocument(
                            session: $session,
                            storedPath: $storedPath,
                            originalName: $originalName,
                            title: $data['title'] ?? null,
                            userId: auth()->id()
                        );

                        Notification::make()
                            ->title('Document enregistré')
                            ->body('Le livrable d\'émargement a été archivé dans la GED.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->path)),

                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
