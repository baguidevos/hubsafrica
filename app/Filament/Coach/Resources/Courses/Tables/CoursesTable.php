<?php

namespace App\Filament\Coach\Resources\Courses\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
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
                    ->label('Palier cible')
                    ->badge()
                    ->color('primary')
                    ->placeholder('Tous paliers')
                    ->sortable(),

                TextColumn::make('modules_count')
                    ->label('Modules')
                    ->counts('modules')
                    ->badge()
                    ->color('info'),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->wrap(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('sort_order');
    }
}
