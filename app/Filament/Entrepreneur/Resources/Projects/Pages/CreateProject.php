<?php

namespace App\Filament\Entrepreneur\Resources\Projects\Pages;

use App\Filament\Entrepreneur\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    /**
     * Guard: prevent creating a second project.
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

        parent::mount();
    }

    /**
     * After creation, redirect to the view page instead of edit.
     */
    protected function getRedirectUrl(): string
    {
        return ProjectResource::getUrl('view', ['record' => $this->record]);
    }
}
