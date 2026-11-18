<?php

namespace App\Filament\Coach\Resources\Courses;

use App\Filament\Coach\Resources\Courses\Pages\ListCourses;
use App\Filament\Coach\Resources\Courses\Pages\PreviewCourseLesson;
use App\Filament\Coach\Resources\Courses\Pages\ViewCourse;
use App\Filament\Coach\Resources\Courses\Tables\CoursesTable;
use App\Models\Course;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static string|UnitEnum|null $navigationGroup = 'Outils & Partages';

    protected static ?string $navigationLabel = 'Cours & Parcours';

    protected static ?string $modelLabel = 'Cours';

    protected static ?string $pluralModelLabel = 'Cours & Parcours';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-play-circle';

    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_published', true);
    }

    public static function table(Table $table): Table
    {
        return CoursesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourses::route('/'),
            'view' => ViewCourse::route('/{record}'),
            'lesson-preview' => PreviewCourseLesson::route('/{record}/lecon'),
        ];
    }
}
