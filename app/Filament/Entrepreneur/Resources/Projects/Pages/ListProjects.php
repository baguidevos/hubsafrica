<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProjects extends ListRecords
{
    protected static string $resource = ProjectResource::class;

    /**
     * Auto-redirect: single project per entrepreneur.
     * If the entrepreneur already has a project → redirect to its view page.
     * If not → redirect to the create page.
     */
    public function mount(): void
    {
        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if ($entrepreneur) {
            $project = Project::where('entrepreneur_id', $entrepreneur->id)->first();

            if ($project) {
                $this->redirect(ProjectResource::getUrl('view', ['record' => $project]));

                return;
            }
        }

        if (ProjectResource::canCreate()) {
            $this->redirect(ProjectResource::getUrl('create'));

            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => ProjectResource::canCreate()),
        ];
    }
}
