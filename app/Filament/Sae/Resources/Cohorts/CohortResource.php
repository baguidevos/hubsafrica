<?php

namespace App\Filament\Sae\Resources\Cohorts;

use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Cohorts\Pages\CreateCohort;
use App\Filament\Sae\Resources\Cohorts\Pages\EditCohort;
use App\Filament\Sae\Resources\Cohorts\Pages\ListCohorts;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortPhasesRelationManager;
use App\Filament\Sae\Resources\Cohorts\Schemas\CohortForm;
use App\Filament\Sae\Resources\Cohorts\Tables\CohortsTable;
use App\Models\Cohort;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CohortResource extends Resource
{
    protected static ?string $model = Cohort::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Cohortes & Promotions';

    protected static ?string $modelLabel = 'Cohorte';

    protected static ?string $pluralModelLabel = 'Cohortes & Promotions';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CohortForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CohortsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CohortPhasesRelationManager::class,
            RelationManagers\CohortSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCohorts::route('/'),
            'create' => CreateCohort::route('/create'),
            'edit' => EditCohort::route('/{record}/edit'),
        ];
    }
}
