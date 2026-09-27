<?php

namespace App\Filament\Sae\Resources\Levels;

use App\Filament\Sae\Resources\Levels\Pages\CreateLevel;
use App\Filament\Sae\Resources\Levels\Pages\EditLevel;
use App\Filament\Sae\Resources\Levels\Pages\ListLevels;
use App\Filament\Sae\Resources\Levels\Schemas\LevelForm;
use App\Filament\Sae\Resources\Levels\Tables\LevelsTable;
use App\Models\Level;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Programmes';

    protected static ?string $navigationLabel = 'Niveaux d\'accompagnement';

    protected static ?string $modelLabel = 'Niveau';

    protected static ?string $pluralModelLabel = 'Niveaux d\'accompagnement';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return LevelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LevelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLevels::route('/'),
            'create' => CreateLevel::route('/create'),
            'edit' => EditLevel::route('/{record}/edit'),
        ];
    }
}


