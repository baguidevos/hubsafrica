<?php

namespace App\Filament\Entrepreneur\Resources\DataRoomDocuments;

use App\Filament\Entrepreneur\Resources\DataRoomDocuments\Pages\ListDataRoomDocuments;
use App\Filament\Entrepreneur\Resources\DataRoomDocuments\Schemas\DataRoomDocumentForm;
use App\Filament\Entrepreneur\Resources\DataRoomDocuments\Tables\DataRoomDocumentsTable;
use App\Models\DataRoomDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DataRoomDocumentResource extends Resource
{
    protected static ?string $model = DataRoomDocument::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Data room';

    protected static ?string $modelLabel = 'Document Data Room';

    protected static ?string $pluralModelLabel = 'Data room';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DataRoomDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataRoomDocumentsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $query->where('entrepreneur_id', $entrepreneur->id);
        }

        return $query;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status !== 'VALIDE';
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status !== 'VALIDE';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataRoomDocuments::route('/'),
        ];
    }
}
