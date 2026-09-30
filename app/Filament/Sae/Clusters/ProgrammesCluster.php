<?php

namespace App\Filament\Sae\Clusters;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Facades\Filament;
use Filament\Pages\Enums\SubNavigationPosition;

class ProgrammesCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Programmes & Sélection';

    protected static ?string $clusterBreadcrumb = 'Programmes';

    protected static ?string $modelLabel = 'Programme & Sélection';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        $count = Application::query()
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
}
