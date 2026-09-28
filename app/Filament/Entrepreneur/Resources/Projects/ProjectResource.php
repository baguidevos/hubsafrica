<?php

namespace App\Filament\Entrepreneur\Resources\Projects;

use App\Filament\Entrepreneur\Resources\Projects\Pages\CreateProject;
use App\Filament\Entrepreneur\Resources\Projects\Pages\EditProject;
use App\Filament\Entrepreneur\Resources\Projects\Pages\ListProjects;
use App\Filament\Entrepreneur\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Entrepreneur\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Mon espace';

    protected static ?string $navigationLabel = 'Mon projet';

    protected static ?string $modelLabel = 'Mon projet';

    protected static ?string $pluralModelLabel = 'Mon projet';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-light-bulb';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
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

    public static function canDelete(Model $record): bool
    {
        return ! $record->applications()->exists() && ! $record->submissions()->exists();
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
