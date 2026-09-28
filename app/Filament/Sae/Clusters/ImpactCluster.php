<?php

namespace App\Filament\Sae\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;

class ImpactCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Impact & Performance';

    protected static ?string $clusterBreadcrumb = 'Impact & Finance';

    protected static ?string $modelLabel = 'Impact & Finance';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?int $navigationSort = 4;
}
