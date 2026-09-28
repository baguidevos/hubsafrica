<?php

namespace App\Filament\Coach\Resources\Applications\Tables;

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('score')
                    ->label('Note Jury')
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
                    ->label('À évaluer en priorité')
                    ->query(fn ($query) => $query->whereIn('status', [ApplicationStatus::SOUMISE, ApplicationStatus::EN_EXAMEN])),

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
                    ->modalHeading(fn (?Application $record) => 'Notation Jury — Candidature de '.($record?->entrepreneur?->full_name ?? 'Candidat'))
                    ->modalDescription('Saisissez vos appréciations critère par critère. Le score global pondéré sera calculé et appliqué automatiquement.')
                    ->modalSubmitActionLabel('Enregistrer mon évaluation')
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

                        $components[] = Select::make('decision_status')
                            ->label('Avis & Recommandation')
                            ->options([
                                'AUTO' => 'Automatique selon la note (≥75 Retenue, 50-74 Liste d\'attente, <50 Refusée)',
                                ApplicationStatus::ACCEPTEE->value => 'Favorable : Retenir pour la cohorte',
                                ApplicationStatus::EN_ATTENTE->value => 'Réserve : Placer sur liste d\'attente',
                                ApplicationStatus::REFUSEE->value => 'Défavorable : Ne pas retenir',
                                ApplicationStatus::EN_EXAMEN->value => 'En examen : Compléments attendus',
                            ])
                            ->default('AUTO');

                        $components[] = Textarea::make('comment')
                            ->label('Commentaire & appréciation du jury')
                            ->placeholder('Synthèse des points forts, faiblesses et recommandation du comité...')
                            ->rows(3);

                        return $components;
                    })
                    ->action(function ($record, array $data) {
                        $evaluatorId = auth()->id() ?? 1;
                        $criteria = $record->program?->evaluationCriteria ?? collect();
                        $decisionChoice = $data['decision_status'] ?? 'AUTO';
                        $forcedStatus = $decisionChoice !== 'AUTO' ? ApplicationStatus::from($decisionChoice) : null;

                        if ($criteria->isEmpty() && isset($data['direct_score'])) {
                            $score = (int) $data['direct_score'];
                            $record->applyEvaluationDecision($score, $forcedStatus, $data['comment'] ?? null);
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

                            if ($forcedStatus !== null) {
                                $freshScore = $record->fresh()->score ?? 0;
                                $record->fresh()->applyEvaluationDecision($freshScore, $forcedStatus, $data['comment'] ?? null);
                            }
                        }

                        $fresh = $record->fresh();
                        Notification::make()
                            ->title('Évaluation jury enregistrée')
                            ->body("Note : {$fresh->score} / 100 — Statut : {$fresh->status->getLabel()}.")
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
