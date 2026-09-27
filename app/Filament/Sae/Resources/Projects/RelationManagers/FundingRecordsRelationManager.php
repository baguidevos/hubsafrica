<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FundingRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'funding';
    protected static ?string $title = 'Financements & Levées';
    protected static string | \BackedEnum | null $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('source')
            ->columns([
                TextColumn::make('source')
                    ->label('Bailleur / Source')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('amount_obtained')
                    ->label('Montant obtenu')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->suffix(' FCFA')
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                IconColumn::make('verified')
                    ->label('Vérifié')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('date', 'desc');
    }
}
