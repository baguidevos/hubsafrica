<?php

namespace App\Filament\Sae\Resources\FundingRecords;

use App\Filament\Sae\Clusters\ImpactCluster;
use App\Filament\Sae\Resources\FundingRecords\Pages\CreateFundingRecord;
use App\Filament\Sae\Resources\FundingRecords\Pages\EditFundingRecord;
use App\Filament\Sae\Resources\FundingRecords\Pages\ListFundingRecords;
use App\Filament\Sae\Resources\FundingRecords\Schemas\FundingRecordForm;
use App\Filament\Sae\Resources\FundingRecords\Tables\FundingRecordsTable;
use App\Models\FundingRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class FundingRecordResource extends Resource
{
    protected static ?string $model = FundingRecord::class;

    protected static ?string $cluster = ImpactCluster::class;

    protected static ?string $navigationLabel = 'Financement';

    protected static ?string $modelLabel = 'Financement';

    protected static ?string $pluralModelLabel = 'Financements';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return FundingRecordForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FundingRecordsTable::configure($table);
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
            'index' => ListFundingRecords::route('/'),
            'create' => CreateFundingRecord::route('/create'),
            'edit' => EditFundingRecord::route('/{record}/edit'),
        ];
    }
}
