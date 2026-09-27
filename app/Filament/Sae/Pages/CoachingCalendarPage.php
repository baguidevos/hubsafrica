<?php

namespace App\Filament\Sae\Pages;

use App\Models\Coach;
use App\Models\CoachingSession;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CoachingCalendarPage extends Page
{
    protected string $view = 'filament.sae.pages.coaching-calendar-page';

    protected static string | \UnitEnum | null $navigationGroup = 'Suivi & évaluation';

    protected static ?string $navigationLabel = 'Calendrier des séances';

    protected static ?string $title = 'Planning & Calendrier du coaching';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 6;

    public int $currentMonth;
    public int $currentYear;
    public ?string $selectedCoachId = 'all';
    public ?int $selectedSessionId = null;

    public function mount(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function previousMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function nextMonth(): void
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
    }

    public function today(): void
    {
        $this->currentMonth = now()->month;
        $this->currentYear = now()->year;
    }

    public function getMonthNameProperty(): string
    {
        return ucfirst(Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->translatedFormat('F Y'));
    }

    public function getCoachesProperty(): Collection
    {
        return Coach::where('status', 'ACTIF')->get();
    }

    public function getDaysProperty(): array
    {
        $start = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $start->copy();

        $query = CoachingSession::with(['coach', 'project', 'entrepreneur'])
            ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

        if ($this->selectedCoachId && $this->selectedCoachId !== 'all') {
            $query->where('coach_id', $this->selectedCoachId);
        }

        $allSessions = $query->get()->groupBy(fn ($s) => Carbon::parse($s->date)->format('Y-m-d'));

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $days[] = [
                'date' => $current->copy(),
                'dateStr' => $dateStr,
                'dayNumber' => $current->day,
                'isCurrentMonth' => $current->month === $this->currentMonth,
                'isToday' => $current->isToday(),
                'sessions' => $allSessions->get($dateStr, collect()),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function openSession(int $id): void
    {
        $this->selectedSessionId = $id;
    }

    public function closeSession(): void
    {
        $this->selectedSessionId = null;
    }

    public function getSelectedSessionProperty(): ?CoachingSession
    {
        if (! $this->selectedSessionId) {
            return null;
        }

        return CoachingSession::with(['coach', 'project', 'entrepreneur'])->find($this->selectedSessionId);
    }
}
