<?php

namespace App\Filament\Coach\Pages;

use Filament\Pages\Page;

class GuidePage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Aide';

    protected static ?string $navigationLabel = 'Guide d\'utilisation';

    protected static ?string $title = 'Guide d\'utilisation Coach';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bookmark-square';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.sae.pages.guide-page';
}
