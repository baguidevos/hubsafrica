<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubmissionDocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Versions du livrable (GED)';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-document-duplicate';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => "v{$state}")
                    ->alignCenter(),

                TextColumn::make('title')
                    ->label('Titre')
                    ->default(fn ($record) => $record->original_name)
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('original_name')
                    ->label('Nom du fichier')
                    ->description(fn ($record) => $record->human_size),

                TextColumn::make('mime_type')
                    ->label('Type MIME')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Date de versement')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('uploader.name')
                    ->label('Auteur')
                    ->default('Porteur'),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->path)),
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('version', 'desc');
    }
}
