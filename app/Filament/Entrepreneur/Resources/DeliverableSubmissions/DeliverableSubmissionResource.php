<?php

namespace App\Filament\Entrepreneur\Resources\DeliverableSubmissions;

use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\CreateDeliverableSubmission;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\EditDeliverableSubmission;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\ListDeliverableSubmissions;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Schemas\DeliverableSubmissionForm;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Tables\DeliverableSubmissionsTable;
use App\Models\DeliverableSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliverableSubmissionResource extends Resource
{
    protected static ?string $model = DeliverableSubmission::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Mes livrables';

    protected static ?string $modelLabel = 'Livrable';

    protected static ?string $pluralModelLabel = 'Mes livrables';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DeliverableSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverableSubmissionsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $query->where('entrepreneur_id', $entrepreneur->id);
        }
        return $query;
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
            'index' => ListDeliverableSubmissions::route('/'),
            'create' => CreateDeliverableSubmission::route('/create'),
            'edit' => EditDeliverableSubmission::route('/{record}/edit'),
        ];
    }
}


