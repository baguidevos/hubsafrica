<?php

namespace App\Filament\Sae\Pages;

use App\Filament\Pages\BaseSharedToolsLibraryPage;
use App\Filament\Sae\Clusters\OutilsCluster;
use BackedEnum;

class SharedToolsLibraryPage extends BaseSharedToolsLibraryPage
{
    protected static ?string $cluster = OutilsCluster::class;

    protected static ?string $navigationLabel = 'Bibliothèque réseau';

    protected static ?string $title = 'Bibliothèque d\'outils du réseau';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 2;
}
