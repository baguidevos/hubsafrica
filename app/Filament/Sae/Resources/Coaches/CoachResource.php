<?php

namespace App\Filament\Sae\Resources\Coaches;

use App\Filament\Sae\Resources\Coaches\Pages\CreateCoach;
use App\Filament\Sae\Resources\Coaches\Pages\EditCoach;
use App\Filament\Sae\Resources\Coaches\Pages\ListCoaches;
use App\Filament\Sae\Resources\Coaches\Schemas\CoachForm;
use App\Filament\Sae\Resources\Coaches\Tables\CoachesTable;
use App\Models\Coach;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoachResource extends Resource
{
    protected static ?string $model = Coach::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Programmes';

    protected static ?string $navigationLabel = 'Coachs & affectations';

    protected static ?string $modelLabel = 'Coach';

    protected static ?string $pluralModelLabel = 'Coachs & affectations';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 4;

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
            \App\Filament\Sae\Resources\Coaches\RelationManagers\AssignedProjectsRelationManager::class,
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


