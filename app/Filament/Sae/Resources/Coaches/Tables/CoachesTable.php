<?php

namespace App\Filament\Sae\Resources\Coaches\Tables;

use App\Enums\CoachAssignmentStatus;
use App\Enums\CoachStatus;
use App\Models\Coach;
use App\Models\CoachAssignment;
use App\Models\Project;
use App\Models\TimelineEvent;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoachesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom du coach')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('active_assignments_count')
                    ->label('Portefeuille actif')
                    ->counts(['assignments as active_assignments_count' => fn ($q) => $q->where('status', CoachAssignmentStatus::ACTIF)])
                    ->badge()
                    ->color(fn ($state, Coach $record): string => (int) $state >= ($record->max_capacity ?? 5) ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state, Coach $record): string => "{$state} / " . ($record->max_capacity ?? 5) . ' startups')
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                IconColumn::make('is_external')
                    ->label('Externe')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('availability')
                    ->label('Disponibilité')
                    ->placeholder('Non précisée')
                    ->limit(25),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachStatus::class),

                TernaryFilter::make('is_external')
                    ->label('Externe'),
            ])
            ->recordActions([
                Action::make('assign_projects')
                    ->label('Affecter')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->modalHeading(fn (Coach $record) => "Affecter des projets à {$record->name}")
                    ->modalDescription(function (Coach $record) {
                        $activeCount = $record->activeAssignmentsCount();
                        $maxCapacity = $record->max_capacity ?? 5;

                        return "Capacité active : {$activeCount} / {$maxCapacity} startups.";
                    })
                    ->form([
                        Select::make('project_ids')
                            ->label('Sélectionner les projets')
                            ->options(function (Coach $record) {
                                $activeProjectIds = $record->assignments()
                                    ->where('status', CoachAssignmentStatus::ACTIF)
                                    ->pluck('project_id');

                                return Project::query()
                                    ->whereNotIn('id', $activeProjectIds)
                                    ->get()
                                    ->mapWithKeys(function (Project $p) {
                                        $entrepreneurName = $p->entrepreneur?->full_name ?? 'N/A';

                                        return [$p->id => "{$p->name} ({$entrepreneurName})"];
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
                    ->action(function (Coach $record, array $data): void {
                        $projectIds = collect($data['project_ids']);

                        if ($projectIds->isEmpty()) {
                            return;
                        }

                        $count = 0;
                        $saeId = $record->sae_id ?? auth()->user()?->sae_id;

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
                                'coach_id' => $record->id,
                                'project_id' => $project->id,
                                'program_id' => $programId,
                                'cohort_id' => $cohortId,
                                'assigned_at' => $data['assigned_at'] ?? now(),
                                'status' => CoachAssignmentStatus::ACTIF,
                            ]);

                            TimelineEvent::create([
                                'sae_id' => $saeId,
                                'project_id' => $project->id,
                                'entrepreneur_id' => $project->entrepreneur_id,
                                'type' => 'COACHING',
                                'title' => "Affectation du coach : {$record->name}",
                                'description' => "Le coach mentor {$record->name} a été assigné pour vous accompagner.",
                                'event_date' => now(),
                            ]);

                            if ($entrepreneurUser = $project->entrepreneur?->user) {
                                Notification::make()
                                    ->title('Coach assigné à votre projet')
                                    ->body("Le mentor {$record->name} a été assigné pour vous accompagner sur le projet {$project->name}.")
                                    ->icon('heroicon-o-academic-cap')
                                    ->success()
                                    ->sendToDatabase($entrepreneurUser);
                            }

                            $count++;
                        }

                        if ($coachUser = $record->user) {
                            Notification::make()
                                ->title('Nouvelles affectations de projet')
                                ->body("Vous avez été affecté à l'accompagnement de {$count} projet(s).")
                                ->icon('heroicon-o-briefcase')
                                ->info()
                                ->sendToDatabase($coachUser);
                        }

                        Notification::make()
                            ->title("{$count} projet(s) affecté(s) au coach {$record->name}")
                            ->success()
                            ->send();
                    }),

                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
