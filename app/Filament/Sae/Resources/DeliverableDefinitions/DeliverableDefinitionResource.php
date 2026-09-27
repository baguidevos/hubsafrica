<?php

namespace App\Filament\Sae\Resources\DeliverableDefinitions;

use App\Filament\Sae\Resources\DeliverableDefinitions\Pages\CreateDeliverableDefinition;
use App\Filament\Sae\Resources\DeliverableDefinitions\Pages\EditDeliverableDefinition;
use App\Filament\Sae\Resources\DeliverableDefinitions\Pages\ListDeliverableDefinitions;
use App\Filament\Sae\Resources\DeliverableDefinitions\Schemas\DeliverableDefinitionForm;
use App\Filament\Sae\Resources\DeliverableDefinitions\Tables\DeliverableDefinitionsTable;
use App\Models\DeliverableDefinition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliverableDefinitionResource extends Resource
{
    protected static ?string $model = DeliverableDefinition::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Suivi & évaluation';

    protected static ?string $navigationLabel = 'Livrables attendus';

    protected static ?string $modelLabel = 'Livrable attendu';

    protected static ?string $pluralModelLabel = 'Livrables attendus';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-check';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DeliverableDefinitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverableDefinitionsTable::configure($table);
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
            'index' => ListDeliverableDefinitions::route('/'),
            'create' => CreateDeliverableDefinition::route('/create'),
            'edit' => EditDeliverableDefinition::route('/{record}/edit'),
        ];
    }
}


