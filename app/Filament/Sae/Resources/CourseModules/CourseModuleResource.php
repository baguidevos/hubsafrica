<?php

namespace App\Filament\Sae\Resources\CourseModules;

use App\Filament\Sae\Resources\CourseModules\Pages\CreateCourseModule;
use App\Filament\Sae\Resources\CourseModules\Pages\EditCourseModule;
use App\Filament\Sae\Resources\CourseModules\Pages\ListCourseModules;
use App\Filament\Sae\Resources\CourseModules\RelationManagers\LessonsRelationManager;
use App\Filament\Sae\Resources\CourseModules\Schemas\CourseModuleForm;
use App\Filament\Sae\Resources\CourseModules\Tables\CourseModulesTable;
use App\Models\CourseModule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CourseModuleResource extends Resource
{
    protected static ?string $model = CourseModule::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'Module';

    protected static ?string $pluralModelLabel = 'Modules';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-folder';

    public static function form(Schema $schema): Schema
    {
        return CourseModuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseModulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            LessonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseModules::route('/'),
            'create' => CreateCourseModule::route('/create'),
            'edit' => EditCourseModule::route('/{record}/edit'),
        ];
    }
}
