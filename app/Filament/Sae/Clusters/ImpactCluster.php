<?php

namespace App\Filament\Sae\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class ImpactCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Impact & Performance';

    protected static ?string $clusterBreadcrumb = 'Impact & Finance';

    protected static ?string $modelLabel = 'Impact & Finance';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 4;
}
