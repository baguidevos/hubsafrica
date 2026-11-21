<?php

namespace App\Filament\Entrepreneur\Resources\DeliverableSubmissions;

use App\Enums\DeliverableSubmissionStatus;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\CreateDeliverableSubmission;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\EditDeliverableSubmission;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Pages\ListDeliverableSubmissions;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Schemas\DeliverableSubmissionForm;
use App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Tables\DeliverableSubmissionsTable;
use App\Filament\Sae\Resources\DeliverableSubmissions\RelationManagers\SubmissionDocumentsRelationManager;
use App\Models\DeliverableSubmission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliverableSubmissionResource extends Resource
{
    protected static ?string $model = DeliverableSubmission::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Mes livrables';

    protected static ?string $modelLabel = 'Livrable';

    protected static ?string $pluralModelLabel = 'Mes livrables';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return DeliverableSubmissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverableSubmissionsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user?->isSuperAdmin()) {
            return $query;
        }

        $entrepreneur = $user?->entrepreneurProfile;

        if (! $entrepreneur) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('entrepreneur_id', $entrepreneur->id);
    }

    public static function canEdit(Model $record): bool
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        return $entrepreneur !== null
            && (int) $record->getAttribute('entrepreneur_id') === (int) $entrepreneur->id
            && $record->status !== DeliverableSubmissionStatus::VALIDE;
    }

    public static function canDelete(Model $record): bool
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        return $entrepreneur !== null
            && (int) $record->getAttribute('entrepreneur_id') === (int) $entrepreneur->id
            && $record->status !== DeliverableSubmissionStatus::VALIDE;
    }

    public static function getRelations(): array
    {
        return [
            SubmissionDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliverableSubmissions::route('/'),
            'create' => CreateDeliverableSubmission::route('/create'),
            'edit' => EditDeliverableSubmission::route('/{record}/edit'),
        ];
    }
}
