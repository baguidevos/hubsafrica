<?php

namespace App\Filament\Sae\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class OutilsCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Outils d\'accompagnement';

    protected static ?string $clusterBreadcrumb = 'Outils';

    protected static ?string $modelLabel = 'Outils d\'accompagnement';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 4;
}
