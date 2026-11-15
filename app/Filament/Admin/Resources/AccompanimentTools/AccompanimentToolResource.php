<?php

namespace App\Filament\Admin\Resources\AccompanimentTools;

use App\Enums\AccompanimentToolAccessLevel;
use App\Filament\Admin\Resources\AccompanimentTools\Pages\ListAdminAccompanimentTools;
use App\Models\AccompanimentTool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AccompanimentToolResource extends Resource
{
    protected static ?string $model = AccompanimentTool::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Outils d\'accompagnement';

    protected static ?string $modelLabel = 'Outil d\'accompagnement';

    protected static ?string $pluralModelLabel = 'Outils d\'accompagnement';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 12;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sae.name')
                    ->label('Structure SAE')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Outil')
                    ->searchable()
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdminAccompanimentTools::route('/'),
        ];
    }
}
