<?php

namespace App\Filament\Sae\Resources\Projects\Pages;

use App\Filament\Sae\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kanban')
                ->label('Vue Kanban (Paliers)')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url(static::getResource()::getUrl('kanban')),
            CreateAction::make(),
        ];
    }
}
