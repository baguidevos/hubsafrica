<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions;

use App\Filament\Sae\Resources\DeliverableSubmissions\Pages\CreateDeliverableSubmission;
use App\Filament\Sae\Resources\DeliverableSubmissions\Pages\EditDeliverableSubmission;
use App\Filament\Sae\Resources\DeliverableSubmissions\Pages\ListDeliverableSubmissions;
use App\Filament\Sae\Resources\DeliverableSubmissions\Schemas\DeliverableSubmissionForm;
use App\Filament\Sae\Resources\DeliverableSubmissions\Tables\DeliverableSubmissionsTable;
use App\Models\DeliverableSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliverableSubmissionResource extends Resource
{
    protected static ?string $model = DeliverableSubmission::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Suivi & évaluation';

    protected static ?string $navigationLabel = 'Livrables déposés';

    protected static ?string $modelLabel = 'Livrable déposé';

    protected static ?string $pluralModelLabel = 'Livrables déposés';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return DeliverableSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverableSubmissionsTable::configure($table);
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


