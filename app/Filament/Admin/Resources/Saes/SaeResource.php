<?php

namespace App\Filament\Admin\Resources\Saes;

use App\Filament\Admin\Resources\Saes\Pages\CreateSae;
use App\Filament\Admin\Resources\Saes\Pages\EditSae;
use App\Filament\Admin\Resources\Saes\Pages\ListSaes;
use App\Filament\Admin\Resources\Saes\Schemas\SaeForm;
use App\Filament\Admin\Resources\Saes\Tables\SaesTable;
use App\Models\Sae;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SaeResource extends Resource
{
    protected static ?string $model = Sae::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Structures (SAE)';

    protected static ?string $modelLabel = 'Structure SAE';

    protected static ?string $pluralModelLabel = 'Structures (SAE)';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return SaeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaesTable::configure($table);
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
            'index' => ListSaes::route('/'),
            'create' => CreateSae::route('/create'),
            'edit' => EditSae::route('/{record}/edit'),
        ];
    }
}


