<?php

namespace App\Filament\Sae\Resources\Indicators\Tables;

use App\Enums\IndicatorCategory;
use App\Enums\IndicatorStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IndicatorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Indicateur')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Catégorie')
                    ->badge()
                    ->sortable(),

                TextColumn::make('target')
                    ->label('Cible')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(fn ($record) => $record->unit === '%' ? ' %' : ($record->unit === 'FCFA' ? ' FCFA' : ''))
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('current')
                    ->label('Actuel')
                    ->numeric(decimalPlaces: 1)
                    ->suffix(fn ($record) => $record->unit === '%' ? ' %' : ($record->unit === 'FCFA' ? ' FCFA' : ''))
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('frequency')
                    ->label('Fréquence')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('responsible')
                    ->label('Responsable')
                    ->searchable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->label('Catégorie')
                    ->options(IndicatorCategory::class),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(IndicatorStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('category');
    }
}
