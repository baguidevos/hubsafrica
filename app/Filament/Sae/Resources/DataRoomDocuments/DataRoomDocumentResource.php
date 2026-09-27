<?php

namespace App\Filament\Sae\Resources\DataRoomDocuments;

use App\Filament\Sae\Resources\DataRoomDocuments\Pages\CreateDataRoomDocument;
use App\Filament\Sae\Resources\DataRoomDocuments\Pages\EditDataRoomDocument;
use App\Filament\Sae\Resources\DataRoomDocuments\Pages\ListDataRoomDocuments;
use App\Filament\Sae\Resources\DataRoomDocuments\Schemas\DataRoomDocumentForm;
use App\Filament\Sae\Resources\DataRoomDocuments\Tables\DataRoomDocumentsTable;
use App\Models\DataRoomDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataRoomDocumentResource extends Resource
{
    protected static ?string $model = DataRoomDocument::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Suivi & évaluation';

    protected static ?string $navigationLabel = 'Data room';

    protected static ?string $modelLabel = 'Document Data Room';

    protected static ?string $pluralModelLabel = 'Data room';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return DataRoomDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataRoomDocumentsTable::configure($table);
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
            'index' => ListDataRoomDocuments::route('/'),
            'create' => CreateDataRoomDocument::route('/create'),
            'edit' => EditDataRoomDocument::route('/{record}/edit'),
        ];
    }
}


