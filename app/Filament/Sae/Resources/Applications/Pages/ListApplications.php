<?php

namespace App\Filament\Sae\Resources\Applications\Pages;

use App\Filament\Sae\Resources\Applications\ApplicationResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApplications extends ListRecords
{
    protected static string $resource = ApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kanban')
                ->label('Vue Kanban')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url(static::getResource()::getUrl('kanban')),
            CreateAction::make(),
        ];
    }
}
