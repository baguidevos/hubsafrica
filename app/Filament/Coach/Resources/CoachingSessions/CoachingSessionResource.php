<?php

namespace App\Filament\Coach\Resources\CoachingSessions;

use App\Filament\Coach\Resources\CoachingSessions\Pages\CreateCoachingSession;
use App\Filament\Coach\Resources\CoachingSessions\Pages\EditCoachingSession;
use App\Filament\Coach\Resources\CoachingSessions\Pages\ListCoachingSessions;
use App\Filament\Coach\Resources\CoachingSessions\Schemas\CoachingSessionForm;
use App\Filament\Coach\Resources\CoachingSessions\Tables\CoachingSessionsTable;
use App\Models\CoachingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoachingSessionResource extends Resource
{
    protected static ?string $model = CoachingSession::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mon accompagnement';

    protected static ?string $navigationLabel = 'Mes séances de coaching';

    protected static ?string $modelLabel = 'Séance de coaching';

    protected static ?string $pluralModelLabel = 'Mes séances de coaching';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CoachingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachingSessionsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();
        $coach = auth()->user()?->coachProfile;
        if ($coach) {
            $query->where('coach_id', $coach->id);
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
            'index' => ListCoachingSessions::route('/'),
            'create' => CreateCoachingSession::route('/create'),
            'edit' => EditCoachingSession::route('/{record}/edit'),
        ];
    }
}


