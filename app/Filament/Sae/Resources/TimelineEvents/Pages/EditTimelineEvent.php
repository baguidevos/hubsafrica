<?php

namespace App\Filament\Sae\Resources\TimelineEvents\Pages;

use App\Filament\Sae\Resources\TimelineEvents\TimelineEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimelineEvent extends EditRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}


