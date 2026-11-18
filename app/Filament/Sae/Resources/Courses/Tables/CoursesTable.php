<?php

namespace App\Filament\Sae\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Cours')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Palier requis')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Tous paliers')
                    ->sortable(),

                TextColumn::make('modules_count')
                    ->label('Modules')
                    ->counts('modules')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level_id')
                    ->label('Palier')
                    ->relationship('level', 'name'),

                TernaryFilter::make('is_published')
                    ->label('Publication'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
