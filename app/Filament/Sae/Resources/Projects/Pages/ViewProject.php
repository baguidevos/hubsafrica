<?php

namespace App\Filament\Sae\Resources\Projects\Pages;

use App\Filament\Sae\Resources\Projects\ProjectResource;
use App\Models\Project;
use Filament\Actions\Action;
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
            $this->getRelationManagersContentComponent(),
        ]);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
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

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('printFiche')
                ->label('Fiche projet A4')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->modalHeading('Dossier & Fiche de synthèse projet')
                ->modalContent(fn ($record) => view('filament.sae.pages.print-project-fiche', ['record' => $record]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer'),
        ];
    }
}
