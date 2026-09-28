<?php

namespace App\Filament\Coach\Resources\DataRoomDocuments;

use App\Filament\Coach\Resources\DataRoomDocuments\Pages\ListDataRoomDocuments;
use App\Filament\Sae\Resources\DataRoomDocuments\Schemas\DataRoomDocumentForm;
use App\Filament\Sae\Resources\DataRoomDocuments\Tables\DataRoomDocumentsTable;
use App\Models\DataRoomDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DataRoomDocumentResource extends Resource
{
    protected static ?string $model = DataRoomDocument::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Outils & Partages';

    protected static ?string $navigationLabel = 'Data room';

    protected static ?string $modelLabel = 'Document Data Room';

    protected static ?string $pluralModelLabel = 'Data room';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?int $navigationSort = 2;

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
        $coach = auth()->user()?->coachProfile;
        if ($coach) {
            $projectIds = $coach->assignments()->where('status', 'ACTIF')->pluck('project_id');
            $query->whereIn('project_id', $projectIds);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataRoomDocuments::route('/'),
        ];
    }
}
