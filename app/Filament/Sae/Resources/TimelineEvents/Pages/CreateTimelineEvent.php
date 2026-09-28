<?php

namespace App\Filament\Sae\Resources\TimelineEvents\Pages;

use App\Filament\Sae\Resources\TimelineEvents\TimelineEventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimelineEvent extends CreateRecord
{
    protected static string $resource = TimelineEventResource::class;
}
