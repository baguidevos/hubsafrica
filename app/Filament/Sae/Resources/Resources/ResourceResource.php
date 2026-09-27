<?php

namespace App\Filament\Sae\Resources\Resources;

use App\Filament\Sae\Resources\Resources\Pages\CreateResource;
use App\Filament\Sae\Resources\Resources\Pages\EditResource;
use App\Filament\Sae\Resources\Resources\Pages\ListResources;
use App\Filament\Sae\Resources\Resources\Schemas\ResourceForm;
use App\Filament\Sae\Resources\Resources\Tables\ResourcesTable;
use App\Models\Resource as ResourceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResourceResource extends Resource
{
    protected static ?string $model = ResourceModel::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Suivi & évaluation';

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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResources::route('/'),
            'create' => CreateResource::route('/create'),
            'edit' => EditResource::route('/{record}/edit'),
        ];
    }
}


