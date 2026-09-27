<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions;

use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\CreateCoachingSession;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\EditCoachingSession;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\ListCoachingSessions;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Schemas\CoachingSessionForm;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Tables\CoachingSessionsTable;
use App\Models\CoachingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoachingSessionResource extends Resource
{
    protected static ?string $model = CoachingSession::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Mon espace';

    protected static ?string $navigationLabel = 'Mon coaching';

    protected static ?string $modelLabel = 'Séance de coaching';

    protected static ?string $pluralModelLabel = 'Mon coaching';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 5;

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
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $query->where('entrepreneur_id', $entrepreneur->id);
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


