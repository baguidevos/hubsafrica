<?php

namespace App\Filament\Sae\Resources\Programs;

use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Programs\Pages\CreateProgram;
use App\Filament\Sae\Resources\Programs\Pages\EditProgram;
use App\Filament\Sae\Resources\Programs\Pages\ListPrograms;
use App\Filament\Sae\Resources\Programs\RelationManagers\EvaluationCriteriaRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\PartnersRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\PhasesRelationManager;
use App\Filament\Sae\Resources\Programs\Schemas\ProgramForm;
use App\Filament\Sae\Resources\Programs\Tables\ProgramsTable;
use App\Models\Program;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Programmes';

    protected static ?string $modelLabel = 'Programme';

    protected static ?string $pluralModelLabel = 'Programmes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PhasesRelationManager::class,
            EvaluationCriteriaRelationManager::class,
            PartnersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrograms::route('/'),
            'create' => CreateProgram::route('/create'),
            'edit' => EditProgram::route('/{record}/edit'),
        ];
    }
}
