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

                TextColumn::make('price')
                    ->label('Tarif')
                    ->formatStateUsing(fn ($record): string => $record->access_level === AccompanimentToolAccessLevel::PAID ? $record->formattedPrice() : '—'),

                IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('access_level')
                    ->label('Niveau d\'accès')
                    ->options(AccompanimentToolAccessLevel::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
