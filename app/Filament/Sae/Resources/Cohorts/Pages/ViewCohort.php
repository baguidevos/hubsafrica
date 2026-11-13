<?php

namespace App\Filament\Sae\Resources\Cohorts\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Resources\Cohorts\CohortResource;
use App\Filament\Sae\Resources\Programs\ProgramResource;
use App\Models\Cohort;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ViewCohort extends ViewRecord
{
    protected static string $resource = CohortResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backToProgram')
                ->label('Voir le programme')
                ->icon('heroicon-o-academic-cap')
                ->color('gray')
                ->visible(fn (): bool => $this->record->program_id !== null && $this->record->program !== null)
                ->url(fn (): string => ProgramResource::getUrl('view', ['record' => $this->record->program])),

            Action::make('copyJoinLink')
                ->label('Lien direct de candidature')
                ->icon('heroicon-o-link')
                ->color('warning')
                ->modalHeading(fn (Cohort $record): string => "Lien de candidature directe — {$record->name}")
                ->form([
                    TextInput::make('join_url')
                        ->label('Lien de candidature directe')
                        ->default(fn (Cohort $record): string => $record->public_join_url)
                        ->readOnly(),
                ])
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fermer')
                ->extraModalFooterActions([
                    Action::make('copyToClipboard')
                        ->label('Copier le lien')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->color('primary')
                        ->action(function ($livewire, Cohort $record): void {
                            $joinUrl = $record->public_join_url;
                            $livewire->js("window.navigator.clipboard.writeText('{$joinUrl}')");

                            Notification::make()
                                ->title('Lien copié !')
                                ->body("Le lien direct pour la cohorte {$record->name} a été copié.")
                                ->success()
                                ->send();
                        }),
                ]),

            EditAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            View::make('filament.sae.resources.cohorts.pages.view-cohort'),
            $this->getRelationManagersContentComponent(),
        ]);
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return false;
    }

    protected function resolveRecord(int|string $key): Model
    {
        /** @var Cohort $cohort */
        $cohort = parent::resolveRecord($key);

        return $cohort
            ->load(['program.level', 'applicationForm'])
            ->loadCount([
                'applications',
                'enrollments',
                'phases',
                'coachingSessions',
                'evaluators',
                'applications as pending_applications_count' => fn (Builder $query): Builder => $query->where('status', ApplicationStatus::SOUMISE),
                'applications as accepted_applications_count' => fn (Builder $query): Builder => $query->where('status', ApplicationStatus::ACCEPTEE),
            ]);
    }
}
