<?php

namespace App\Filament\Sae\Resources\FundingRecords\Tables;

use App\Enums\FundingStatus;
use App\Enums\FundingType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FundingRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projet')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('source')
                    ->label('Source / Bailleur')
                    ->searchable()
                    ->default('Non précisée'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('amount_obtained')
                    ->label('Montant obtenu')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->suffix(' FCFA')
                    ->sortable()
                    ->alignRight(),

                TextColumn::make('amount_sought')
                    ->label('Sollicité')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->suffix(' FCFA')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('amount_invested')
                    ->label('Investi')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->suffix(' FCFA')
                    ->sortable()
                    ->alignRight()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                IconColumn::make('verified')
                    ->label('Vérifié')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(FundingStatus::class),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options(FundingType::class),

                SelectFilter::make('project_id')
                    ->label('Projet')
                    ->relationship('project', 'name'),

                TernaryFilter::make('verified')
                    ->label('Audit / Vérification'),
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
            ->defaultSort('date', 'desc');
    }
}
