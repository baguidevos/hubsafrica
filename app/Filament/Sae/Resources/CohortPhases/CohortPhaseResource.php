<?php

namespace App\Filament\Sae\Resources\CohortPhases;

use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\CohortPhases\Pages\CreateCohortPhase;
use App\Filament\Sae\Resources\CohortPhases\Pages\EditCohortPhase;
use App\Filament\Sae\Resources\CohortPhases\Pages\ListCohortPhases;
use App\Filament\Sae\Resources\CohortPhases\Schemas\CohortPhaseForm;
use App\Filament\Sae\Resources\CohortPhases\Tables\CohortPhasesTable;
use App\Models\CohortPhase;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CohortPhaseResource extends Resource
{
    protected static ?string $model = CohortPhase::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Jalons & Phases';

    protected static ?string $modelLabel = 'Jalon de cohorte';

    protected static ?string $pluralModelLabel = 'Jalons & Phases';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CohortPhaseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CohortPhasesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCohortPhases::route('/'),
            'create' => CreateCohortPhase::route('/create'),
            'edit' => EditCohortPhase::route('/{record}/edit'),
        ];
    }
}
