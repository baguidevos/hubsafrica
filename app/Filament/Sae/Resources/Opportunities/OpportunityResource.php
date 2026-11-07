<?php

namespace App\Filament\Sae\Resources\Opportunities;

use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Opportunities\Pages\CreateOpportunity;
use App\Filament\Sae\Resources\Opportunities\Pages\EditOpportunity;
use App\Filament\Sae\Resources\Opportunities\Pages\ListOpportunities;
use App\Filament\Sae\Resources\Opportunities\Schemas\OpportunityForm;
use App\Filament\Sae\Resources\Opportunities\Tables\OpportunitiesTable;
use App\Models\Opportunity;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class OpportunityResource extends Resource
{
    protected static ?string $model = Opportunity::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Opportunités & Appels';

    protected static ?string $modelLabel = 'Opportunité';

    protected static ?string $pluralModelLabel = 'Opportunités & Appels';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        $openOpportunitiesCount = Opportunity::query()
            ->when($tenant, fn ($query) => $query->where('sae_id', $tenant->id))
            ->open()
            ->count();

        return $openOpportunitiesCount > 0 ? (string) $openOpportunitiesCount : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }

    public static function form(Schema $schema): Schema
    {
        return OpportunityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OpportunitiesTable::configure($table);
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
            'index' => ListOpportunities::route('/'),
            'create' => CreateOpportunity::route('/create'),
            'edit' => EditOpportunity::route('/{record}/edit'),
        ];
    }
}
