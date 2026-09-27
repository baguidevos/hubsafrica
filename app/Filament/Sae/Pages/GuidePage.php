<?php

namespace App\Filament\Sae\Pages;

use Filament\Pages\Page;

class GuidePage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Guide d\'utilisation';

    protected static ?string $title = 'Guide d\'utilisation & FAQ SIGAE';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.sae.pages.guide-page';
}


