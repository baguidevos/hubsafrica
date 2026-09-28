<?php

namespace App\Filament\Sae\Pages;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Models\Coach;
use App\Models\CoachingSession;
use App\Models\Cohort;
use App\Models\CohortPhase;
use BackedEnum;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class CoachingCalendarPage extends Page
{
    protected string $view = 'filament.sae.pages.coaching-calendar-page';

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Calendrier & Planning global';

    protected static ?string $title = 'Planning & Calendrier du coaching, jalons & cohortes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?int $navigationSort = 7;

    public int $currentMonth;

    public int $currentYear;

    public ?string $selectedCoachId = 'all';

    public ?string $selectedCohortId = 'all';

    public ?string $selectedEventType = 'all';

    public ?int $selectedSessionId = null;

    public ?int $selectedPhaseId = null;

    public ?int $selectedCohortDetailId = null;

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

    public function getCohortsProperty(): Collection
    {
        return Cohort::orderBy('name')->get();
    }

    public function getDaysProperty(): array
    {
        $start = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $eventsByDate = [];

        // 1. Séances de coaching
        if ($this->selectedEventType === 'all' || $this->selectedEventType === 'sessions') {
            $sessionQuery = CoachingSession::with(['coach', 'project', 'entrepreneur', 'cohort'])
                ->whereBetween('date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);

            if ($this->selectedCoachId && $this->selectedCoachId !== 'all') {
                $sessionQuery->where('coach_id', $this->selectedCoachId);
            }

            if ($this->selectedCohortId && $this->selectedCohortId !== 'all') {
                $sessionQuery->where('cohort_id', $this->selectedCohortId);
            }

            foreach ($sessionQuery->get() as $s) {
                $d = Carbon::parse($s->date)->format('Y-m-d');
                $typeVal = is_object($s->type) ? $s->type->value : $s->type;
                $eventsByDate[$d][] = [
                    'id' => $s->id,
                    'kind' => 'session',
                    'time' => Carbon::parse($s->date)->format('H:i'),
                    'title' => $s->theme ?: ($s->project?->name ?: 'Séance coaching'),
                    'subtitle' => $s->coach?->name,
                    'tag' => $typeVal,
                    'color' => match ($typeVal) {
                        'COLLECTIF' => 'emerald',
                        'MENTORAT' => 'rose',
                        'ATELIER' => 'amber',
                        default => 'teal',
                    },
                    'raw' => $s,
                ];
            }
        }

        // 2. Jalons & Phases de Cohorte (Starts on & Ends on)
        if ($this->selectedEventType === 'all' || $this->selectedEventType === 'phases') {
            $phaseQuery = CohortPhase::with(['cohort.program'])
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('starts_on', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                        ->orWhereBetween('ends_on', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
                });

            if ($this->selectedCohortId && $this->selectedCohortId !== 'all') {
                $phaseQuery->where('cohort_id', $this->selectedCohortId);
            }

            foreach ($phaseQuery->get() as $p) {
                if ($p->starts_on) {
                    $d = Carbon::parse($p->starts_on)->format('Y-m-d');
                    $eventsByDate[$d][] = [
                        'id' => $p->id,
                        'kind' => 'phase',
                        'time' => 'Jalon #'.$p->position,
                        'title' => '🚩 Début : '.$p->name,
                        'subtitle' => $p->cohort?->name,
                        'tag' => 'DÉBUT JALON',
                        'color' => 'indigo',
                        'raw' => $p,
                    ];
                }
                if ($p->ends_on) {
                    $d = Carbon::parse($p->ends_on)->format('Y-m-d');
                    $eventsByDate[$d][] = [
                        'id' => $p->id,
                        'kind' => 'phase',
                        'time' => 'Échéance',
                        'title' => '🏁 Fin : '.$p->name,
                        'subtitle' => $p->cohort?->name,
                        'tag' => 'ÉCHÉANCE',
                        'color' => 'purple',
                        'raw' => $p,
                    ];
                }
            }
        }

        // 3. Lancements et Clôtures de Cohortes
        if ($this->selectedEventType === 'all' || $this->selectedEventType === 'cohorts') {
            $cohortQuery = Cohort::with('program')
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                        ->orWhereBetween('end_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()]);
                });

            if ($this->selectedCohortId && $this->selectedCohortId !== 'all') {
                $cohortQuery->where('id', $this->selectedCohortId);
            }

            foreach ($cohortQuery->get() as $c) {
                if ($c->start_date) {
                    $d = Carbon::parse($c->start_date)->format('Y-m-d');
                    $eventsByDate[$d][] = [
                        'id' => $c->id,
                        'kind' => 'cohort',
                        'time' => 'Lancement',
                        'title' => '🚀 Lancement : '.$c->name,
                        'subtitle' => $c->program?->name,
                        'tag' => 'PROMOTION',
                        'color' => 'blue',
                        'raw' => $c,
                    ];
                }
                if ($c->end_date) {
                    $d = Carbon::parse($c->end_date)->format('Y-m-d');
                    $eventsByDate[$d][] = [
                        'id' => $c->id,
                        'kind' => 'cohort',
                        'time' => 'Clôture',
                        'title' => '🎓 Demo Day / Clôture : '.$c->name,
                        'subtitle' => $c->program?->name,
                        'tag' => 'DEMO DAY',
                        'color' => 'amber',
                        'raw' => $c,
                    ];
                }
            }
        }

        $days = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $dayEvents = collect($eventsByDate[$dateStr] ?? []);

            $days[] = [
                'date' => $current->copy(),
                'dateStr' => $dateStr,
                'dayNumber' => $current->day,
                'isCurrentMonth' => $current->month === $this->currentMonth,
                'isToday' => $current->isToday(),
                'events' => $dayEvents,
                // Rétro-compatibilité pour les sessions
                'sessions' => $dayEvents->where('kind', 'session'),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function openSession(int $id): void
    {
        $this->selectedSessionId = $id;
        $this->selectedPhaseId = null;
        $this->selectedCohortDetailId = null;
    }

    public function openPhase(int $id): void
    {
        $this->selectedPhaseId = $id;
        $this->selectedSessionId = null;
        $this->selectedCohortDetailId = null;
    }

    public function openCohort(int $id): void
    {
        $this->selectedCohortDetailId = $id;
        $this->selectedSessionId = null;
        $this->selectedPhaseId = null;
    }

    public function closeModal(): void
    {
        $this->selectedSessionId = null;
        $this->selectedPhaseId = null;
        $this->selectedCohortDetailId = null;
    }

    public function getSelectedSessionProperty(): ?CoachingSession
    {
        if (! $this->selectedSessionId) {
            return null;
        }

        return CoachingSession::with(['coach', 'project', 'entrepreneur', 'cohort'])->find($this->selectedSessionId);
    }

    public function getSelectedPhaseProperty(): ?CohortPhase
    {
        if (! $this->selectedPhaseId) {
            return null;
        }

        return CohortPhase::with(['cohort.program', 'programPhase'])->find($this->selectedPhaseId);
    }

    public function getSelectedCohortDetailProperty(): ?Cohort
    {
        if (! $this->selectedCohortDetailId) {
            return null;
        }

        return Cohort::with(['program', 'phases'])->find($this->selectedCohortDetailId);
    }
}
