<?php

namespace App\Filament\Sae\Resources\Resources\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titre')
                    ->weight('bold')
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

                TextColumn::make('level.name')
                    ->label('Niveau')
                    ->badge()
                    ->color('primary')
                    ->default('Tous niveaux')
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Thématique')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('visibility')
                    ->label('Accès')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'TOUS' => 'Tous',
                        'ENTREPRENEUR_ONLY' => 'Entrepreneurs',
                        'COACH_ONLY' => 'Coaches',
                        'INTERNAL' => 'Interne',
                        default => $state,
                    })
                    ->color('gray'),

                TextColumn::make('author')
                    ->label('Auteur')
                    ->searchable()
                    ->placeholder('—'),
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
                    ->color('info')
                    ->url(fn ($record) => $record->url ?: ($record->file_path ? asset('storage/'.$record->file_path) : null))
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->url) || filled($record->file_path)),

                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
