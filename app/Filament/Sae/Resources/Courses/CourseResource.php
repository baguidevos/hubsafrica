<?php

namespace App\Filament\Sae\Resources\Courses;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Filament\Sae\Resources\Courses\Pages\CreateCourse;
use App\Filament\Sae\Resources\Courses\Pages\EditCourse;
use App\Filament\Sae\Resources\Courses\Pages\ListCourses;
use App\Filament\Sae\Resources\Courses\Pages\PreviewCourseLesson;
use App\Filament\Sae\Resources\Courses\Pages\ViewCourse;
use App\Filament\Sae\Resources\Courses\RelationManagers\ModulesRelationManager;
use App\Filament\Sae\Resources\Courses\Schemas\CourseForm;
use App\Filament\Sae\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Cours & Parcours';

    protected static ?string $modelLabel = 'Cours';

    protected static ?string $pluralModelLabel = 'Cours & Parcours';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return CourseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ModulesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'create' => CreateCourse::route('/create'),
            'view' => ViewCourse::route('/{record}'),
            'lesson-preview' => PreviewCourseLesson::route('/{record}/lecon'),
            'edit' => EditCourse::route('/{record}/edit'),
        ];
    }
}
