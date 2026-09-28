<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    protected string $view = 'filament.entrepreneur.pages.view-project';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier mon projet')
                ->icon('heroicon-o-pencil-square'),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load([
            'currentLevel',
            'latestEnrollment.cohort',
            'latestApplication',
            'entrepreneur',
            'sessions',
            'submissions',
            'assignments.coach',
        ]);
    }
}
