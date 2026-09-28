<?php

namespace App\Filament\Sae\Pages;

use Filament\Pages\Page;

class GuidePage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Pilotage';

    protected static ?string $navigationLabel = 'Guide d\'utilisation SAE';

    protected static ?string $title = 'Guide Méthodologique & Pratique de l\'Accompagnateur SAE';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.sae.pages.guide-page';
}
