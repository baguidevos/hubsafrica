<?php

namespace App\Filament\Sae\Resources\AccompanimentTools;

use App\Filament\Sae\Clusters\OutilsCluster;
use App\Filament\Sae\Resources\AccompanimentTools\Pages\CreateAccompanimentTool;
use App\Filament\Sae\Resources\AccompanimentTools\Pages\EditAccompanimentTool;
use App\Filament\Sae\Resources\AccompanimentTools\Pages\ListAccompanimentTools;
use App\Filament\Sae\Resources\AccompanimentTools\Schemas\AccompanimentToolForm;
use App\Filament\Sae\Resources\AccompanimentTools\Tables\AccompanimentToolsTable;
use App\Models\AccompanimentTool;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class AccompanimentToolResource extends Resource
{
    protected static ?string $model = AccompanimentTool::class;

    protected static ?string $cluster = OutilsCluster::class;

    protected static ?string $navigationLabel = 'Outils partagés';

    protected static ?string $modelLabel = 'Outil d\'accompagnement';

    protected static ?string $pluralModelLabel = 'Outils d\'accompagnement';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return AccompanimentToolForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccompanimentToolsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccompanimentTools::route('/'),
            'create' => CreateAccompanimentTool::route('/create'),
            'edit' => EditAccompanimentTool::route('/{record}/edit'),
        ];
    }
}
