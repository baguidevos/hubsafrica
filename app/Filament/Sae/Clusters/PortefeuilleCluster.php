<?php

namespace App\Filament\Sae\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;

class PortefeuilleCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Portefeuille Startups';

    protected static ?string $clusterBreadcrumb = 'Portefeuille';

    protected static ?string $modelLabel = 'Portefeuille';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 2;
}
