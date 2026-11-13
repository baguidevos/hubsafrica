<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
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
            EditAction::make()
                ->label('Modifier mon projet')
                ->icon('heroicon-o-pencil-square'),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        /** @var Project $project */
        $project = parent::resolveRecord($key);

        return $project
            ->load([
                'initialLevel',
                'currentLevel',
                'exitLevel',
                'levelHistories.fromLevel',
                'levelHistories.toLevel',
                'levelHistories.validator',
                'latestEnrollment.cohort.program',
                'latestApplication',
                'entrepreneur',
                'assignments.coach',
            ])
            ->loadCount(['sessions', 'submissions']);
    }
}
