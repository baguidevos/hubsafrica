<?php

namespace App\Filament\Sae\Resources\CoachJoinRequests\Pages;

use App\Filament\Sae\Resources\CoachJoinRequests\CoachJoinRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListCoachJoinRequests extends ListRecords
{
    protected static string $resource = CoachJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
