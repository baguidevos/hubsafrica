<?php

namespace App\Filament\Coach\Pages;

use App\Filament\Pages\BaseSharedToolsLibraryPage;
use BackedEnum;
use UnitEnum;

class SharedToolsLibraryPage extends BaseSharedToolsLibraryPage
{
    protected static string|UnitEnum|null $navigationGroup = 'Outils & Partages';

    protected static ?string $navigationLabel = 'Bibliothèque d\'outils';

    protected static ?string $title = 'Bibliothèque d\'outils du réseau';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-library';

    protected static ?int $navigationSort = 3;
}
