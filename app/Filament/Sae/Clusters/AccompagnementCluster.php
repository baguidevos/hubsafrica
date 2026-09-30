<?php

namespace App\Filament\Sae\Clusters;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Pages\Enums\SubNavigationPosition;

class AccompagnementCluster extends Cluster
{
    protected static ?string $navigationLabel = 'Accompagnement & Mentorat';

    protected static ?string $clusterBreadcrumb = 'Accompagnement';

    protected static ?string $modelLabel = 'Accompagnement & Mentorat';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?int $navigationSort = 3;
}
