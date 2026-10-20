<?php

namespace App\Filament\Sae\Resources\DeliverableSubmissions\Pages;

use App\Filament\Sae\Resources\DeliverableSubmissions\DeliverableSubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliverableSubmissions extends ListRecords
{
    protected static string $resource = DeliverableSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kanban')
                ->label('Vue Kanban')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url(static::getResource()::getUrl('kanban')),
            CreateAction::make()->slideOver(),
        ];
    }
}
