<?php

namespace App\Filament\Coach\Resources\Projects;

use App\Filament\Coach\Resources\Projects\Pages\CreateProject;
use App\Filament\Coach\Resources\Projects\Pages\EditProject;
use App\Filament\Coach\Resources\Projects\Pages\ListProjects;
use App\Filament\Coach\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Coach\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mon accompagnement';

    protected static ?string $navigationLabel = 'Mes projets assignés';

    protected static ?string $modelLabel = 'Projet assigné';

    protected static ?string $pluralModelLabel = 'Mes projets assignés';

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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $coach = auth()->user()?->coachProfile;
        if ($coach) {
            $projectIds = $coach->assignments()->where('status', 'ACTIF')->pluck('project_id');
            $query->whereIn('id', $projectIds);
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
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}


