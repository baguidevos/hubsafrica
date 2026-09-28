<?php

namespace App\Filament\Coach\Resources\Applications\Pages;

use App\Enums\ApplicationEvaluationStatus;
use App\Enums\ApplicationStatus;
use App\Filament\Coach\Resources\Applications\ApplicationResource;
use App\Filament\Coach\Resources\Projects\ProjectResource;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationEvaluationScore;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    protected string $view = 'filament.common.view-application';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('evaluate')
                ->label('Noter le dossier (Jury)')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->modalHeading(fn (Application $record) => 'Notation Jury — Candidature de '.($record->entrepreneur?->full_name ?? 'Candidat'))
                ->modalDescription('Saisissez vos appréciations critère par critère. Le score global pondéré sera calculé et appliqué automatiquement.')
                ->modalSubmitActionLabel('Enregistrer mon évaluation')
                ->form(function (Application $record) {
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
                        ->label('Commentaire & appréciation du coach / jury')
                        ->placeholder('Points forts, axes d\'amélioration, adéquation avec le programme...')
                        ->rows(3);

                    return $components;
                })
                ->action(function (Application $record, array $data) {
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
                        ->body("Votre note a été prise en compte (Moyenne : {$fresh->score}/100 — Statut : {$fresh->status->getLabel()}).")
                        ->success()
                        ->send();
                }),

            Action::make('viewProject')
                ->label('Fiche Projet')
                ->icon('heroicon-o-light-bulb')
                ->color('gray')
                ->visible(fn () => $this->record->project_id !== null && $this->record->project !== null)
                ->url(fn () => ProjectResource::getUrl('edit', ['record' => $this->record->project])),
        ];
    }

    protected function resolveRecord(int|string $key): Model
    {
        return parent::resolveRecord($key)->load([
            'program.sae',
            'cohort',
            'project.currentLevel',
            'form',
            'reviewer',
            'evaluations.evaluator',
            'evaluations.scores.criterion',
            'documents',
            'entrepreneur',
        ]);
    }
}
