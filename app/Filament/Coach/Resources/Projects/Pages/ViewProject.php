<?php

namespace App\Filament\Coach\Resources\Projects\Pages;

use App\Filament\Coach\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ViewProject extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.common.view-project'),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        /** @var Project $project */
        $project = parent::resolveRecord($key);

        return $project
            ->load([
                'entrepreneur',
                'initialLevel',
                'currentLevel',
                'exitLevel',
                'levelHistories.fromLevel',
                'levelHistories.toLevel',
                'levelHistories.validator',
                'latestEnrollment.cohort.program',
                'assignments.coach',
            ])
            ->loadCount(['sessions', 'submissions']);
    }
}
