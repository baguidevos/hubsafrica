<?php

namespace App\Filament\Sae\Widgets;

use App\Models\TimelineEvent;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class RecentTimelineEventsWidget extends Widget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'lg' => 1,
    ];
    protected string $view = 'filament.sae.widgets.recent-timeline-events-widget';

    public function getRecentEvents(): array
    {
        $events = TimelineEvent::latest()
            ->limit(6)
            ->get();

        return $events->map(function ($e) {
            return [
                'id' => $e->id,
                'title' => $e->title,
                'description' => $e->description,
                'time_ago' => Carbon::parse($e->created_at)->diffForHumans(),
                'type' => $e->type,
                'icon' => $e->icon ?: 'heroicon-m-sparkles',
            ];
        })->toArray();
    }
}
