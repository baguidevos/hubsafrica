<?php

namespace App\Filament\Entrepreneur\Widgets;

use App\Enums\FormUnlockStatus;
use App\Models\FormUnlock;
use Filament\Widgets\Widget;

class EntrepreneurPendingSurveysWidget extends Widget
{
    protected static ?int $sort = 0;

    protected string $view = 'filament.entrepreneur.widgets.entrepreneur-pending-surveys-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if (! $entrepreneur) {
            return false;
        }

        return FormUnlock::where('entrepreneur_id', $entrepreneur->id)
            ->where('status', '!=', FormUnlockStatus::SOUMIS)
            ->exists();
    }

    public function getViewData(): array
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        $pendingUnlocks = $entrepreneur
            ? FormUnlock::with(['form.program'])
                ->where('entrepreneur_id', $entrepreneur->id)
                ->where('status', '!=', FormUnlockStatus::SOUMIS)
                ->orderBy('due_date', 'asc')
                ->get()
            : collect();

        return [
            'pendingUnlocks' => $pendingUnlocks,
        ];
    }
}
