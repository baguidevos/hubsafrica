<?php

namespace App\Filament\Admin\Resources\SaeTypes;

use App\Filament\Admin\Resources\SaeTypes\Pages\CreateSaeType;
use App\Filament\Admin\Resources\SaeTypes\Pages\EditSaeType;
use App\Filament\Admin\Resources\SaeTypes\Pages\ListSaeTypes;
use App\Filament\Admin\Resources\SaeTypes\Schemas\SaeTypeForm;
use App\Filament\Admin\Resources\SaeTypes\Tables\SaeTypesTable;
use App\Models\SaeType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SaeTypeResource extends Resource
{
    protected static ?string $model = SaeType::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Types de SAE';

    protected static ?string $modelLabel = 'Type de SAE';

    protected static ?string $pluralModelLabel = 'Types de SAE';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SaeTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaeTypesTable::configure($table);
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
            'index' => ListSaeTypes::route('/'),
            'create' => CreateSaeType::route('/create'),
            'edit' => EditSaeType::route('/{record}/edit'),
        ];
    }
}
