<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Enums\FormUnlockStatus;
use App\Models\FormUnlock;
use Filament\Pages\Page;

class QuestionnairesPage extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Mes dépôts';

    protected static ?string $navigationLabel = 'Mes questionnaires';

    protected static ?string $title = 'Mes questionnaires & Enquêtes';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.entrepreneur.pages.questionnaires-page';

    public static function getNavigationBadge(): ?string
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if (! $entrepreneur) {
            return null;
        }

        $count = FormUnlock::where('entrepreneur_id', $entrepreneur->id)
            ->where('status', '!=', FormUnlockStatus::SOUMIS)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if (! $entrepreneur) {
            return null;
        }

        $hasOverdue = FormUnlock::where('entrepreneur_id', $entrepreneur->id)
            ->where('status', '!=', FormUnlockStatus::SOUMIS)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->exists();

        return $hasOverdue ? 'danger' : 'warning';
    }

    public function getViewData(): array
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if (! $entrepreneur) {
            return [
                'pendingUnlocks' => collect(),
                'completedUnlocks' => collect(),
                'totalCount' => 0,
                'pendingCount' => 0,
                'completedCount' => 0,
            ];
        }

        $pendingUnlocks = FormUnlock::with(['form.program'])
            ->where('entrepreneur_id', $entrepreneur->id)
            ->where('status', '!=', FormUnlockStatus::SOUMIS)
            ->orderBy('due_date', 'asc')
            ->get();

        $completedUnlocks = FormUnlock::with(['form.program', 'submission'])
            ->where('entrepreneur_id', $entrepreneur->id)
            ->where('status', FormUnlockStatus::SOUMIS)
            ->orderBy('completed_at', 'desc')
            ->get();

        return [
            'pendingUnlocks' => $pendingUnlocks,
            'completedUnlocks' => $completedUnlocks,
            'totalCount' => $pendingUnlocks->count() + $completedUnlocks->count(),
            'pendingCount' => $pendingUnlocks->count(),
            'completedCount' => $completedUnlocks->count(),
        ];
    }
}
