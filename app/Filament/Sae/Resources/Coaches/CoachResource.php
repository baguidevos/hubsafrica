<?php

namespace App\Filament\Sae\Resources\Coaches;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Filament\Sae\Resources\Coaches\Pages\CreateCoach;
use App\Filament\Sae\Resources\Coaches\Pages\EditCoach;
use App\Filament\Sae\Resources\Coaches\Pages\ListCoaches;
use App\Filament\Sae\Resources\Coaches\RelationManagers\AssignedProjectsRelationManager;
use App\Filament\Sae\Resources\Coaches\Schemas\CoachForm;
use App\Filament\Sae\Resources\Coaches\Tables\CoachesTable;
use App\Models\Coach;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CoachResource extends Resource
{
    protected static ?string $model = Coach::class;

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Annuaire des coachs';

    protected static ?string $modelLabel = 'Coach';

    protected static ?string $pluralModelLabel = 'Annuaire des coachs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CoachForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AssignedProjectsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoaches::route('/'),
            'create' => CreateCoach::route('/create'),
            'edit' => EditCoach::route('/{record}/edit'),
        ];
    }
}
