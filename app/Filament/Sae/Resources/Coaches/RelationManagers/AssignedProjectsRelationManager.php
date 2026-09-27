<?php

namespace App\Filament\Sae\Resources\Coaches\RelationManagers;

use App\Enums\CoachAssignmentStatus;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Project;
use App\Models\TimelineEvent;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssignedProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Projets accompagnés & Affectations';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-light-bulb';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Projet')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->searchable()
                    ->color('gray'),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->placeholder('Hors cohorte'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('assigned_at')
                    ->label('Date d\'affectation')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Fin d\'affectation')
                    ->date('d/m/Y')
                    ->placeholder('En cours'),
            ])
            ->headerActions([
                Action::make('assign_project')
                    ->label('Affecter à des projets')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Affecter ce coach à des projets')
                    ->modalDescription(function () {
                        /** @var Coach $coach */
                        $coach = $this->getOwnerRecord();
                        $activeCount = $coach->activeAssignmentsCount();
                        $maxCapacity = $coach->max_capacity ?? 5;

                        return "Coach : {$coach->name} — Charge active : {$activeCount} / {$maxCapacity} startups.";
                    })
                    ->form([
                        Select::make('project_ids')
                            ->label('Projets à assigner')
                            ->options(function () {
                                /** @var Coach $coach */
                                $coach = $this->getOwnerRecord();
                                $activeProjectIds = $coach->assignments()
                                    ->where('status', CoachAssignmentStatus::ACTIF)
                                    ->pluck('project_id');

                                return Project::query()
                                    ->whereNotIn('id', $activeProjectIds)
                                    ->get()
                                    ->mapWithKeys(function (Project $project) {
                                        $entrepreneurName = $project->entrepreneur?->full_name ?? 'N/A';

                                        return [$project->id => "{$project->name} ({$entrepreneurName})"];
                                    });
                            })
                            ->multiple()
                            ->required()
                            ->searchable(),

                        DatePicker::make('assigned_at')
                            ->label('Date d\'affectation')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Coach $coach */
                        $coach = $this->getOwnerRecord();
                        $projectIds = collect($data['project_ids']);

                        if ($projectIds->isEmpty()) {
                            return;
                        }

                        $count = 0;
                        $saeId = $coach->sae_id ?? auth()->user()?->sae_id;

                        foreach ($projectIds as $projectId) {
                            $project = Project::with(['enrollments.cohort', 'entrepreneur.user'])->find($projectId);

                            if (! $project) {
                                continue;
                            }

                            $latestEnrollment = $project->enrollments()->latest()->first();
                            $cohortId = $latestEnrollment?->cohort_id;
                            $programId = $cohortId ? $latestEnrollment->cohort?->program_id : null;

                            CoachAssignment::create([
                                'sae_id' => $saeId,
                                'coach_id' => $coach->id,
                                'project_id' => $project->id,
                                'program_id' => $programId,
                                'cohort_id' => $cohortId,
                                'assigned_at' => $data['assigned_at'] ?? now(),
                                'status' => CoachAssignmentStatus::ACTIF,
                            ]);

                            // Timeline event on the project
                            TimelineEvent::create([
                                'sae_id' => $saeId,
                                'project_id' => $project->id,
                                'entrepreneur_id' => $project->entrepreneur_id,
                                'type' => 'COACHING',
                                'title' => "Affectation du coach : {$coach->name}",
                                'description' => "Le coach mentor {$coach->name} a été officiellement assigné à l'accompagnement de votre projet.",
                                'event_date' => now(),
                            ]);

                            // Notification to the entrepreneur
                            if ($entrepreneurUser = $project->entrepreneur?->user) {
                                Notification::make()
                                    ->title('Coach assigné à votre projet')
                                    ->body("Le mentor {$coach->name} a été assigné pour vous accompagner sur le projet {$project->name}.")
                                    ->icon('heroicon-o-academic-cap')
                                    ->success()
                                    ->sendToDatabase($entrepreneurUser);
                            }

                            $count++;
                        }

                        // Notification to the coach
                        if ($coachUser = $coach->user) {
                            Notification::make()
                                ->title('Nouvelles affectations de projet')
                                ->body("Vous avez été affecté à l'accompagnement de {$count} nouveau(x) projet(s).")
                                ->icon('heroicon-o-briefcase')
                                ->info()
                                ->sendToDatabase($coachUser);
                        }

                        Notification::make()
                            ->title("{$count} projet(s) affecté(s) au coach {$coach->name}")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('end_assignment')
                    ->label('Clore')
                    ->icon('heroicon-o-check-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Mettre fin à l\'affectation')
                    ->modalDescription(fn (CoachAssignment $record) => "Clôturer l'affectation du coach avec le projet {$record->project?->name} ?")
                    ->visible(fn (CoachAssignment $record): bool => $record->status === CoachAssignmentStatus::ACTIF)
                    ->action(function (CoachAssignment $record): void {
                        $record->update([
                            'status' => CoachAssignmentStatus::TERMINE,
                            'ended_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Affectation clôturée avec succès')
                            ->success()
                            ->send();
                    }),

                ViewAction::make()->slideOver(),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->defaultSort('assigned_at', 'desc');
    }
}
