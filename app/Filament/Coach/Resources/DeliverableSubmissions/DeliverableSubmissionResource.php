<?php

namespace App\Filament\Coach\Resources\DeliverableSubmissions;

use App\Filament\Coach\Resources\DeliverableSubmissions\Pages\CreateDeliverableSubmission;
use App\Filament\Coach\Resources\DeliverableSubmissions\Pages\EditDeliverableSubmission;
use App\Filament\Coach\Resources\DeliverableSubmissions\Pages\ListDeliverableSubmissions;
use App\Filament\Coach\Resources\DeliverableSubmissions\Schemas\DeliverableSubmissionForm;
use App\Filament\Coach\Resources\DeliverableSubmissions\Tables\DeliverableSubmissionsTable;
use App\Models\DeliverableSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeliverableSubmissionResource extends Resource
{
    protected static ?string $model = DeliverableSubmission::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mon accompagnement';

    protected static ?string $navigationLabel = 'Évaluations des livrables';

    protected static ?string $modelLabel = 'Livrable à évaluer';

    protected static ?string $pluralModelLabel = 'Évaluations des livrables';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 3;

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
        $coach = auth()->user()?->coachProfile;
        if ($coach) {
            $projectIds = $coach->assignments()->where('status', 'ACTIF')->pluck('project_id');
            $query->whereIn('project_id', $projectIds);
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


