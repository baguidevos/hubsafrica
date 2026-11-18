<?php

namespace App\Filament\Sae\Resources\AccompanimentTools\Tables;

use App\Enums\AccompanimentToolAccessLevel;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccompanimentToolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Outil')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('format')
                    ->label('Format')
                    ->badge(),

                TextColumn::make('access_level')
                    ->label('Niveau d\'accès')
                    ->badge(),

                TextColumn::make('saeTypes.name')
                    ->label('Types de SAE')
                    ->badge()
                    ->color('info')
                    ->placeholder('Tous types'),

                TextColumn::make('price')
                    ->label('Tarif')
                    ->formatStateUsing(fn ($record): string => $record->access_level === AccompanimentToolAccessLevel::PAID ? $record->formattedPrice() : '—'),

                IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),

                IconColumn::make('is_shared_with_network')
                    ->label('Partagé réseau')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('access_level')
                    ->label('Niveau d\'accès')
                    ->options(AccompanimentToolAccessLevel::class),

                SelectFilter::make('saeTypes')
                    ->label('Type de SAE')
                    ->relationship('saeTypes', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
