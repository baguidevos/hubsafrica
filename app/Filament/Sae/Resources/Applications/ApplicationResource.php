<?php

namespace App\Filament\Sae\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Applications\Pages\CreateApplication;
use App\Filament\Sae\Resources\Applications\Pages\EditApplication;
use App\Filament\Sae\Resources\Applications\Pages\ListApplications;
use App\Filament\Sae\Resources\Applications\RelationManagers\ApplicationEvaluationsRelationManager;
use App\Filament\Sae\Resources\Applications\RelationManagers\DocumentsRelationManager;
use App\Filament\Sae\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Sae\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Candidatures & Sélection';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Candidatures & Sélection';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        $count = static::getModel()::query()
            ->when($tenant, fn ($query) => $query->where('sae_id', $tenant->id))
            ->where('status', ApplicationStatus::SOUMISE)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Nouvelles candidatures non traitées';
    }

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationEvaluationsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'create' => CreateApplication::route('/create'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
