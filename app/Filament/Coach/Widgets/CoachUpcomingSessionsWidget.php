<?php

namespace App\Filament\Coach\Widgets;

use App\Models\CoachingSession;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class CoachUpcomingSessionsWidget extends Widget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.coach.widgets.coach-upcoming-sessions-widget';

    public function getCoachSessions(): array
    {
        $coach = auth()->user()?->coach;
        if (! $coach) {
            return [];
        }

        $sessions = CoachingSession::with(['project', 'entrepreneur'])
            ->where('coach_id', $coach->id)
            ->where('date', '>=', now()->subHours(2))
            ->orderBy('date', 'asc')
            ->limit(6)
            ->get();

        return $sessions->map(function ($s) {
            $date = Carbon::parse($s->date);
            $diffDays = now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false);

            if ($diffDays === 0) {
                $dayLabel = 'Auj.';
                $accent = true;
            } elseif ($diffDays === 1) {
                $dayLabel = 'Demain';
                $accent = true;
            } else {
                $dayLabel = ucfirst($date->translatedFormat('D'));
                $accent = false;
            }

            return [
                'id' => $s->id,
                'theme' => $s->theme ?: 'Séance de coaching',
                'project' => $s->project?->name ?: 'Projet non spécifié',
                'entrepreneur' => $s->entrepreneur?->full_name ?: ($s->project?->entrepreneur?->full_name ?: '—'),
                'time' => $date->format('H:i'),
                'duration' => $s->duration.' min',
                'dayLabel' => $dayLabel,
                'dayNum' => $date->format('d M'),
                'accent' => $accent,
                'type' => $s->type,
            ];
        })->toArray();
    }
}
