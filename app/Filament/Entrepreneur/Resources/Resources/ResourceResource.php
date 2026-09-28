<?php

namespace App\Filament\Entrepreneur\Resources\Resources;

use App\Filament\Entrepreneur\Resources\Resources\Pages\ListResources;
use App\Filament\Entrepreneur\Resources\Resources\Tables\ResourcesTable;
use App\Filament\Sae\Resources\Resources\Schemas\ResourceForm;
use App\Models\Resource as ResourceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Ressources';

    protected static ?string $modelLabel = 'Ressource';

    protected static ?string $pluralModelLabel = 'Ressources';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ResourceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResourcesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereIn('visibility', ['TOUS', 'ENTREPRENEUR_ONLY']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
        ];
    }
}
