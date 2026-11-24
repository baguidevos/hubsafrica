<?php

namespace App\Filament\Sae\Resources\FormAttempts;

use App\Enums\FormAttemptStatus;
use App\Filament\Sae\Clusters\ImpactCluster;
use App\Filament\Sae\Resources\FormAttempts\Pages\ListFormAttempts;
use App\Filament\Sae\Resources\FormAttempts\Tables\FormAttemptsTable;
use App\Models\FormAttempt;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class FormAttemptResource extends Resource
{
    protected static ?string $model = FormAttempt::class;

    protected static ?string $cluster = ImpactCluster::class;

    protected static ?string $navigationLabel = 'Formulaires non finalisés';

    protected static ?string $modelLabel = 'Saisie de formulaire';

    protected static ?string $pluralModelLabel = 'Formulaires non finalisés';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?int $navigationSort = 5;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', '!=', FormAttemptStatus::SOUMIS->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return FormAttemptsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormAttempts::route('/'),
        ];
    }
}
