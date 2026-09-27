<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

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

class CoachAssignmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'assignments';

    protected static ?string $title = 'Coachs assignés & Mentors';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-academic-cap';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('coach.name')
                    ->label('Coach / Mentor')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('coach.email')
                    ->label('Email')
                    ->color('gray'),

                TextColumn::make('coach.specialties')
                    ->label('Spécialités')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Généraliste'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('assigned_at')
                    ->label('Affecté le')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Fin d\'affectation')
                    ->date('d/m/Y')
                    ->placeholder('En cours'),
            ])
            ->headerActions([
                Action::make('assign_coach')
                    ->label('Assigner un coach')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Assigner un coach à ce projet')
                    ->modalDescription(function () {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();

                        return "Projet cible : {$project->name} — Porteur : " . ($project->entrepreneur?->full_name ?? 'N/A');
                    })
                    ->form([
                        Select::make('coach_id')
                            ->label('Sélectionner le mentor')
                            ->options(function () {
                                /** @var Project $project */
                                $project = $this->getOwnerRecord();

                                $activeCoachIds = $project->assignments()
                                    ->where('status', CoachAssignmentStatus::ACTIF)
                                    ->pluck('coach_id');

                                return Coach::query()
                                    ->whereNotIn('id', $activeCoachIds)
                                    ->where('status', 'ACTIF')
                                    ->get()
                                    ->mapWithKeys(function (Coach $c) {
                                        $activeCount = $c->activeAssignmentsCount();
                                        $max = $c->max_capacity ?? 5;
                                        $specialties = ! empty($c->specialties) ? ' — ' . implode(', ', (array) $c->specialties) : '';

                                        return [$c->id => "{$c->name} ({$activeCount}/{$max} startups){$specialties}"];
                                    });
                            })
                            ->required()
                            ->searchable(),

                        DatePicker::make('assigned_at')
                            ->label('Date d\'affectation')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();
                        $coach = Coach::find($data['coach_id']);

                        if (! $project || ! $coach) {
                            return;
                        }

                        $saeId = $project->sae_id ?? auth()->user()?->sae_id;
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

                        // Timeline event
                        TimelineEvent::create([
                            'sae_id' => $saeId,
                            'project_id' => $project->id,
                            'entrepreneur_id' => $project->entrepreneur_id,
                            'type' => 'COACHING',
                            'title' => "Affectation du coach : {$coach->name}",
                            'description' => "Le coach mentor {$coach->name} a été assigné pour vous accompagner sur le projet {$project->name}.",
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

                        // Notification to the coach
                        if ($coachUser = $coach->user) {
                            Notification::make()
                                ->title('Nouveau projet assigné')
                                ->body("Vous avez été affecté à l'accompagnement du projet {$project->name}.")
                                ->icon('heroicon-o-briefcase')
                                ->info()
                                ->sendToDatabase($coachUser);
                        }

                        Notification::make()
                            ->title("Coach {$coach->name} assigné avec succès")
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
                    ->modalDescription(fn (CoachAssignment $record) => "Clôturer l'accompagnement du coach {$record->coach?->name} pour ce projet ?")
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
                DeleteAction::make()->label('Révoquer'),
            ]);
    }
}
