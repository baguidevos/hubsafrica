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

                        $components[] = Select::make('decision_status')
                            ->label('Décision & Statut de la candidature')
                            ->options([
                                'AUTO' => 'Automatique selon la note (≥75 Retenue, 50-74 Liste d\'attente, <50 Refusée)',
                                ApplicationStatus::ACCEPTEE->value => 'Acceptée / Retenue pour la cohorte',
                                ApplicationStatus::EN_ATTENTE->value => 'Placer sur liste d\'attente',
                                ApplicationStatus::REFUSEE->value => 'Refusée / Non retenue',
                                ApplicationStatus::EN_EXAMEN->value => 'Maintenir En examen',
                            ])
                            ->default('AUTO')
                            ->helperText('Actualise automatiquement le statut de la candidature et enrôle le projet dans la cohorte en cas d\'admission.');

                        $components[] = Textarea::make('comment')
                            ->label('Commentaire & appréciation globale du jury')
                            ->placeholder('Synthèse des points forts, faiblesses et recommandation du comité...')
                            ->rows(3);

                        return $components;
                    })
                    ->action(function ($record, array $data) {
                        $evaluatorId = auth()->id() ?? $record->sae->owner_id ?? 1;
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
                            ->title('Évaluation enregistrée')
                            ->body("Note : {$fresh->score} / 100 — Statut : {$fresh->status->getLabel()}. Projet actualisé.")
                            ->success()
                            ->send();
                    }),
                Action::make('accept')
                    ->label('Accepter')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Accepter la candidature')
                    ->modalDescription(fn (Application $record) => "Valider l'admission de {$record->entrepreneur?->full_name} ({$record->project?->name}) dans la cohorte {$record->cohort?->name} ? Le projet sera automatiquement enrôlé et activé.")
                    ->modalSubmitActionLabel('Confirmer l\'admission')
                    ->visible(fn (Application $record) => $record->status !== ApplicationStatus::ACCEPTEE)
                    ->action(function (Application $record) {
                        $record->applyEvaluationDecision($record->score ?? 80, ApplicationStatus::ACCEPTEE, 'Admission validée par le comité SAE');
                        Notification::make()
                            ->title('Candidature acceptée')
                            ->body("Le projet « {$record->project?->name} » a été admis et enrôlé dans la cohorte {$record->cohort?->name}.")
                            ->success()
                            ->send();
                    }),
                Action::make('waitlist')
                    ->label('Liste d\'attente')
                    ->icon('heroicon-o-clock')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Mettre en liste d\'attente')
                    ->modalDescription(fn (Application $record) => "Placer la candidature de {$record->entrepreneur?->full_name} ({$record->project?->name}) sur liste d'attente ?")
                    ->modalSubmitActionLabel('Placer en attente')
                    ->visible(fn (Application $record) => in_array($record->status, [ApplicationStatus::SOUMISE, ApplicationStatus::EN_EXAMEN]))
                    ->action(function (Application $record) {
                        $record->applyEvaluationDecision($record->score ?? 50, ApplicationStatus::EN_ATTENTE, 'Dossier placé sur liste d\'attente');
                        Notification::make()
                            ->title('Candidature en attente')
                            ->body("Le dossier a été placé sur liste d'attente.")
                            ->warning()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Refuser la candidature')
                    ->modalDescription(fn (Application $record) => "Confirmer le rejet de la candidature de {$record->entrepreneur?->full_name} ({$record->project?->name}) ?")
                    ->modalSubmitActionLabel('Confirmer le refus')
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Motif de refus')
                            ->placeholder('Ex: Projet ne répondant pas aux critères d\'éligibilité du programme...')
                            ->required(),
                    ])
                    ->visible(fn (Application $record) => $record->status !== ApplicationStatus::REFUSEE)
                    ->action(function (Application $record, array $data) {
                        $record->update(['rejection_reason' => $data['rejection_reason']]);
                        $record->applyEvaluationDecision($record->score ?? 40, ApplicationStatus::REFUSEE, $data['rejection_reason']);
                        Notification::make()
                            ->title('Candidature refusée')
                            ->body('La candidature a été marquée comme refusée.')
                            ->danger()
                            ->send();
                    }),
                ViewAction::make(),
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
