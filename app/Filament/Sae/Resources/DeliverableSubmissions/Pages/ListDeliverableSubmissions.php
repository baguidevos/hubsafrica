<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions\Pages;

use App\Filament\Sae\Resources\DeliverableSubmissions\DeliverableSubmissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliverableSubmissions extends ListRecords
{
    protected static string $resource = DeliverableSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->slideOver(),
        ];
    }
}
