<?php

namespace App\Filament\Sae\Resources\Indicators;

use App\Filament\Sae\Clusters\ImpactCluster;
use App\Filament\Sae\Resources\Indicators\Pages\CreateIndicator;
use App\Filament\Sae\Resources\Indicators\Pages\EditIndicator;
use App\Filament\Sae\Resources\Indicators\Pages\ListIndicators;
use App\Filament\Sae\Resources\Indicators\RelationManagers\IndicatorEntriesRelationManager;
use App\Filament\Sae\Resources\Indicators\Schemas\IndicatorForm;
use App\Filament\Sae\Resources\Indicators\Tables\IndicatorsTable;
use App\Models\Indicator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class IndicatorResource extends Resource
{
    protected static ?string $model = Indicator::class;

    protected static ?string $cluster = ImpactCluster::class;

    protected static ?string $navigationLabel = 'Indicateurs S&E';

    protected static ?string $modelLabel = 'Indicateur S&E';

    protected static ?string $pluralModelLabel = 'Indicateurs S&E';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return IndicatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndicatorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            IndicatorEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIndicators::route('/'),
            'create' => CreateIndicator::route('/create'),
            'edit' => EditIndicator::route('/{record}/edit'),
        ];
    }
}
