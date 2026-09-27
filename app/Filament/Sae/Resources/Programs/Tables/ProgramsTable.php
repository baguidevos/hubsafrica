<?php

namespace App\Filament\Sae\Resources\Programs\Tables;

use App\Enums\ProgramStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Programme')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Niveau CUBE')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->default('Transversal'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('target')
                    ->label('Cible')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('capacity')
                    ->label('Capacité')
                    ->suffix(' startups')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('registration_end')
                    ->label('Clôture dépôts')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Démarrage')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ProgramStatus::class),

                SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),
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
            ->defaultSort('created_at', 'desc');
    }
}
