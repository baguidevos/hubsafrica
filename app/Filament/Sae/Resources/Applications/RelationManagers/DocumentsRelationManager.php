<?php

namespace App\Filament\Sae\Resources\Applications\RelationManagers;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'GED & Pièces du dossier';

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
                    ->label('Fichier')
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
                    ->label('Date d\'ajout')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->path)),
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
