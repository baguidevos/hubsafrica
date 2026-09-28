<?php

namespace App\Filament\Sae\Widgets;

use App\Models\CoachingSession;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class UpcomingSessionsWidget extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];

    protected string $view = 'filament.sae.widgets.upcoming-sessions-widget';

    public function getUpcomingSessions(): array
    {
        $sessions = CoachingSession::with(['coach', 'project'])
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
                'theme' => $s->theme ?: 'Séance de coaching',
                'coach' => $s->coach?->name ?: 'Coach assigné',
                'project' => $s->project?->name ?: 'Sans projet',
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
