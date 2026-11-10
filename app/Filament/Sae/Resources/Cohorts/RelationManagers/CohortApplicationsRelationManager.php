<?php

namespace App\Filament\Sae\Resources\Cohorts\RelationManagers;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Resources\Applications\ApplicationResource;
use App\Models\Application;
use App\Models\Cohort;
use App\Models\Project;
use App\Services\ProgramEvaluationPresenter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CohortApplicationsRelationManager extends RelationManager
{
    protected static string $relationship = 'applications';

    protected static ?string $title = 'Candidatures de la cohorte';

    protected static string|BackedEnum|null $icon = 'heroicon-o-clipboard-document-list';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['entrepreneur', 'project', 'program.sae']))
            ->recordUrl(fn (Application $record): string => ApplicationResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur candidat')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name', 'email'])
                    ->description(fn (Application $record): ?string => $record->entrepreneur?->email)
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet / Startup')
                    ->weight('bold')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('project.sector')
                    ->label('Secteur')
                    ->badge()
                    ->color('primary')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('score')
                    ->label('Note Jury')
                    ->badge()
                    ->color(fn ($state, Application $record): string => ProgramEvaluationPresenter::determineScoreBadgeColor(
                        $state !== null ? (int) $state : null,
                        $record->getAdmissionThreshold(),
                        $record->getWaitlistThreshold()
                    ))
                    ->formatStateUsing(fn ($state): string => ProgramEvaluationPresenter::formatScoreLabel(
                        $state !== null ? (int) $state : null
                    ))
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('unprocessed')
                    ->label('Nouvelles non traitées uniquement')
                    ->query(fn (Builder $query): Builder => $query->where('status', ApplicationStatus::SOUMISE)),

                SelectFilter::make('status')
                    ->label('Statut de la candidature')
                    ->options(ApplicationStatus::class),

                SelectFilter::make('sector')
                    ->label('Secteur d\'activité')
                    ->options(fn (): array => $this->resolveCohortSectorOptions())
                    ->query(function (Builder $query, array $data): Builder {
                        $selectedSector = $data['value'] ?? null;
                        if (blank($selectedSector)) {
                            return $query;
                        }

                        return $query->whereHas(
                            'project',
                            fn (Builder $projectQuery): Builder => $projectQuery->where('sector', $selectedSector)
                        );
                    }),
            ])
            ->recordActions([
                Action::make('inspectApplication')
                    ->label('Voir candidature')
                    ->icon('heroicon-o-eye')
                    ->color('primary')
                    ->url(fn (Application $record): string => ApplicationResource::getUrl('view', ['record' => $record])),

                Action::make('acceptApplication')
                    ->label('Accepter')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Accepter la candidature')
                    ->modalDescription(fn (Application $record): string => "Valider l'admission de {$record->entrepreneur?->full_name} ({$record->project?->name}) dans la cohorte {$record->cohort?->name} ?")
                    ->visible(fn (Application $record): bool => $record->status !== ApplicationStatus::ACCEPTEE)
                    ->action(function (Application $record): void {
                        $record->applyEvaluationDecision(
                            $record->score ?? $record->getAdmissionThreshold(),
                            ApplicationStatus::ACCEPTEE,
                            'Admission validée depuis la fiche de la cohorte'
                        );

                        Notification::make()
                            ->title('Candidature acceptée')
                            ->body("Le projet « {$record->project?->name} » a été admis dans la cohorte {$record->cohort?->name}.")
                            ->success()
                            ->send();
                    }),

                Action::make('rejectApplication')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Refuser la candidature')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motif de refus')
                            ->placeholder('Précisez la raison du refus...')
                            ->required(),
                    ])
                    ->visible(fn (Application $record): bool => $record->status !== ApplicationStatus::REFUSEE)
                    ->action(function (Application $record, array $data): void {
                        $rejectionReason = (string) ($data['rejection_reason'] ?? '');
                        $record->update(['rejection_reason' => $rejectionReason]);
                        $record->applyEvaluationDecision(
                            $record->score ?? 40,
                            ApplicationStatus::REFUSEE,
                            $rejectionReason
                        );

                        Notification::make()
                            ->title('Candidature refusée')
                            ->body('Le statut du dossier a été mis à jour.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    /**
     * @return array<string, string>
     */
    private function resolveCohortSectorOptions(): array
    {
        /** @var Cohort $cohort */
        $cohort = $this->getOwnerRecord();

        return Project::query()
            ->whereHas('applications', fn (Builder $query): Builder => $query->where('cohort_id', $cohort->id))
            ->whereNotNull('sector')
            ->where('sector', '!=', '')
            ->distinct()
            ->orderBy('sector')
            ->pluck('sector', 'sector')
            ->all();
    }
}
