<?php

namespace App\Filament\Sae\Resources\Applications\Tables;

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur candidat')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name'])
                    ->description(fn (Application $record) => $record->status === ApplicationStatus::SOUMISE ? 'Nouvelle candidature non traitée' : null)
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->default('—'),

                TextColumn::make('program.name')
                    ->label('Programme')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->default('Non assignée'),

                TextColumn::make('form.name')
                    ->label('Template')
                    ->badge()
                    ->color(fn (?Application $record) => $record?->form?->is_standard ? 'warning' : 'primary')
                    ->default('Standard Hub Africa')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('score')
                    ->label('Note Jury')
                    ->suffix(' / 100')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 75 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non notée')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Déposée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('unprocessed')
                    ->label('Non traitées uniquement')
                    ->query(fn ($query) => $query->where('status', ApplicationStatus::SOUMISE)),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ApplicationStatus::class),

                SelectFilter::make('program_id')
                    ->label('Programme')
                    ->relationship('program', 'name'),

                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name'),
            ])
            ->recordActions([
                Action::make('evaluate')
                    ->label('Noter')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->visible(function () {
                        if (Filament::getCurrentPanel()?->getId() === 'entrepreneur') {
                            return false;
                        }

                        $user = auth()->user();
                        if (! $user) {
                            return false;
                        }

                        return $user->isSuperAdmin()
                            || $user->coachProfile()->exists()
                            || $user->saes()->wherePivotIn('role', ['RESPONSABLE', 'COACH'])->exists()
                            || $user->ownedSae()->exists();
                    })
                    ->modalHeading(fn (?Application $record) => 'Notation Jury — Candidature de '.($record?->entrepreneur?->full_name ?? 'Candidat'))
                    ->modalDescription('Saisissez les notes critère par critère. Le score global pondéré sera calculé et appliqué automatiquement.')
                    ->modalSubmitActionLabel('Valider l\'évaluation')
                    ->form(function (?Application $record) {
                        if (! $record) {
                            return [];
                        }

                        $components = [];
                        $criteria = $record->program?->evaluationCriteria()->orderBy('position')->get() ?? collect();

                        if ($criteria->isEmpty()) {
                            $components[] = TextInput::make('direct_score')
                                ->label('Score direct (sur 100)')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(100)
                                ->default($record->score)
                                ->helperText('Aucun critère spécifique n\'a été configuré dans ce programme.');
                        } else {
                            // Chercher l'évaluation existante de l'utilisateur connecté
                            $existingEval = ApplicationEvaluation::where('application_id', $record->id)
                                ->where('evaluator_id', auth()->id())
                                ->first();

                            foreach ($criteria as $criterion) {
                                $existingScore = $existingEval
                                    ? ApplicationEvaluationScore::where('evaluation_id', $existingEval->id)
                                        ->where('criterion_id', $criterion->id)
                                        ->value('score')
                                    : null;

                                $components[] = TextInput::make("criterion_{$criterion->id}")
                                    ->label("{$criterion->name} (sur {$criterion->max_score} pts — Coeff {$criterion->weight}x)")
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue($criterion->max_score)
                                    ->default($existingScore)
                                    ->helperText($criterion->description)
                                    ->required();
                            }
                        }

                        $components[] = Textarea::make('comment')
                            ->label('Commentaire & appréciation globale du jury')
                            ->placeholder('Synthèse des points forts, faiblesses et recommandation du comité...')
                            ->rows(3);

                        return $components;
                    })
                    ->action(function ($record, array $data) {
                        $evaluatorId = auth()->id() ?? $record->sae->owner_id ?? 1;
                        $criteria = $record->program?->evaluationCriteria ?? collect();

                        if ($criteria->isEmpty() && isset($data['direct_score'])) {
                            $record->update(['score' => (int) $data['direct_score']]);
                        } else {
                            $evaluation = ApplicationEvaluation::updateOrCreate(
                                [
                                    'sae_id' => $record->sae_id,
                                    'application_id' => $record->id,
                                    'evaluator_id' => $evaluatorId,
                                ],
                                [
                                    'comment' => $data['comment'] ?? null,
                                    'status' => ApplicationEvaluationStatus::SOUMIS,
                                    'submitted_at' => now(),
                                ]
                            );

                            foreach ($criteria as $criterion) {
                                $field = "criterion_{$criterion->id}";
                                if (isset($data[$field])) {
                                    ApplicationEvaluationScore::updateOrCreate(
                                        [
                                            'evaluation_id' => $evaluation->id,
                                            'criterion_id' => $criterion->id,
                                        ],
                                        [
                                            'score' => (float) $data[$field],
                                        ]
                                    );
                                }
                            }

                            $evaluation->calculateWeightedScore();
                        }

                        Notification::make()
                            ->title('Évaluation enregistrée')
                            ->body("La note a été recalculée avec succès ({$record->fresh()->score} / 100).")
                            ->success()
                            ->send();
                    }),
                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
