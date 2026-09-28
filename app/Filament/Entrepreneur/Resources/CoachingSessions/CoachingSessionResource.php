<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions;

use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\CreateCoachingSession;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\EditCoachingSession;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Pages\ListCoachingSessions;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Schemas\CoachingSessionForm;
use App\Filament\Entrepreneur\Resources\CoachingSessions\Tables\CoachingSessionsTable;
use App\Models\CoachingSession;
use App\Models\Enrollment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoachingSessionResource extends Resource
{
    protected static ?string $model = CoachingSession::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Mon espace';

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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $entrepreneur = auth()->user()?->entrepreneurProfile;
        if ($entrepreneur) {
            $cohortIds = Enrollment::where('entrepreneur_id', $entrepreneur->id)
                ->whereIn('status', ['ACCEPTE', 'EN_COURS', 'VALIDE', 'ACTIF'])
                ->pluck('cohort_id');

            $query->where(function ($q) use ($entrepreneur, $cohortIds) {
                $q->where('entrepreneur_id', $entrepreneur->id)
                    ->orWhereHas('attendees', fn ($att) => $att->where('entrepreneur_id', $entrepreneur->id))
                    ->orWhereIn('cohort_id', $cohortIds);
            });
        }

        return $query;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return $record->type === \App\Enums\CoachingSessionType::INDIVIDUEL
            && $record->status === \App\Enums\CoachingSessionStatus::PLANIFIEE
            && $record->entrepreneur_id === auth()->user()?->entrepreneurProfile?->id;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
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
