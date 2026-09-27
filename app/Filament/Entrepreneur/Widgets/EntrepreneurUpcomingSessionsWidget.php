<?php

namespace App\Filament\Entrepreneur\Widgets;

use App\Models\CoachingSession;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class EntrepreneurUpcomingSessionsWidget extends Widget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected string $view = 'filament.entrepreneur.widgets.entrepreneur-upcoming-sessions-widget';

    public function getEntrepreneurSessions(): array
    {
        $entrepreneur = auth()->user()?->entrepreneur;
        if (! $entrepreneur) {
            return [];
        }

        $sessions = CoachingSession::with(['coach', 'project'])
            ->where(function ($q) use ($entrepreneur) {
                $q->where('entrepreneur_id', $entrepreneur->id)
                    ->orWhereHas('project', function ($p) use ($entrepreneur) {
                        $p->where('entrepreneur_id', $entrepreneur->id);
                    });
            })
            ->where('date', '>=', now()->subHours(2))
            ->orderBy('date', 'asc')
            ->limit(5)
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
                'theme' => $s->theme ?: 'Séance de coaching individuel',
                'coach' => $s->coach?->name ?: 'Votre coach référent',
                'coach_email' => $s->coach?->email,
                'time' => $date->format('H:i'),
                'duration' => $s->duration . ' min',
                'dayLabel' => $dayLabel,
                'dayNum' => $date->format('d M'),
                'accent' => $accent,
                'type' => $s->type,
                'objectives' => $s->objectives,
            ];
        })->toArray();
    }
}
