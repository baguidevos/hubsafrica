<?php

namespace App\Filament\Entrepreneur\Resources\Applications\Pages;

use App\Filament\Entrepreneur\Resources\Applications\ApplicationResource;
use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.common.view-application';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewProject')
                ->label('Voir le projet lié')
                ->icon('heroicon-o-light-bulb')
                ->color('emerald')
                ->visible(fn () => $this->record->project_id !== null && $this->record->project !== null)
                ->url(fn () => ProjectResource::getUrl('view', ['record' => $this->record->project])),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load([
            'program.sae',
            'cohort',
            'project.currentLevel',
            'form',
            'reviewer',
            'evaluations.evaluator',
            'evaluations.scores.criterion',
            'documents',
        ]);
    }
}
