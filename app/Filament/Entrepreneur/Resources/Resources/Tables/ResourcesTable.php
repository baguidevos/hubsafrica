<?php

namespace App\Filament\Entrepreneur\Resources\Resources\Tables;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight('bold')
                    ->description(fn ($record) => $record->description ? Str::limit($record->description, 60) : null)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Format')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'DOCUMENT' => 'Document',
                        'VIDEO' => 'Vidéo',
                        'LIEN_EXTERNE' => 'Lien web',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'DOCUMENT',
                        'danger' => 'VIDEO',
                        'warning' => 'LIEN_EXTERNE',
                    ])
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Thématique')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->default('Général'),

                TextColumn::make('level.name')
                    ->label('Niveau recommandé')
                    ->badge()
                    ->color('primary')
                    ->default('Tous niveaux')
                    ->sortable(),

                TextColumn::make('author')
                    ->label('Auteur / Source')
                    ->searchable()
                    ->placeholder('Incubateur'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Format')
                    ->options([
                        'DOCUMENT' => 'Document',
                        'VIDEO' => 'Vidéo',
                        'LIEN_EXTERNE' => 'Lien web',
                    ]),

                SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Consulter')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('primary')
                    ->url(fn ($record) => $record->url ?: ($record->file_path ? asset('storage/'.$record->file_path) : null))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->url) || filled($record->file_path)),

                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
