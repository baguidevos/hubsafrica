<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Models\FormUnlock;
use Filament\Pages\Page;

class QuestionnairesPage extends Page
{
    protected static string | \UnitEnum | null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Mes questionnaires';

    protected static ?string $title = 'Mes questionnaires & Enquêtes';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.entrepreneur.pages.questionnaires-page';

    public function getViewData(): array
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        $unlocks = $entrepreneur
            ? FormUnlock::where('entrepreneur_id', $entrepreneur->id)->latest()->get()
            : collect();

        return [
            'unlocks' => $unlocks,
        ];
    }
}


