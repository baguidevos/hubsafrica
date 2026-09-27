<?php

namespace App\Filament\Coach\Resources\DeliverableSubmissions\Tables;

use App\Enums\DeliverableSubmissionStatus;
use App\Models\DeliverableSubmission;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliverableSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('definition.name')
                    ->label('Livrable attendu')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->searchable(['first_name', 'last_name'])
                    ->default('—'),

                TextColumn::make('version')
                    ->label('Version')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => "v{$state}")
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('submitted_at')
                    ->label('Déposé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(DeliverableSubmissionStatus::class),

                SelectFilter::make('definition_id')
                    ->label('Livrable')
                    ->relationship('definition', 'name'),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Fichier')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->file_path)),

                Action::make('evaluate')
                    ->label('Évaluer')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('primary')
                    ->slideOver()
                    ->form([
                        Select::make('status')
                            ->label('Décision d\'évaluation')
                            ->options([
                                DeliverableSubmissionStatus::VALIDE->value => 'Valider le livrable',
                                DeliverableSubmissionStatus::CORRECTION_DEMANDEE->value => 'Demander des corrections',
                                DeliverableSubmissionStatus::REJETEE->value => 'Rejeter le livrable',
                                DeliverableSubmissionStatus::EN_EVALUATION->value => 'Mettre en cours d\'examen',
                            ])
                            ->required(),
                        TextInput::make('score')
                            ->label('Note attribuée')
                            ->numeric()
                            ->suffix('/ 100')
                            ->maxValue(100)
                            ->minValue(0),
                        Textarea::make('feedback')
                            ->label('Commentaires & Recommandations coach')
                            ->rows(4)
                            ->required()
                            ->placeholder('Vos retours détaillés pour guider l\'entrepreneur...'),
                    ])
                    ->action(function (DeliverableSubmission $record, array $data): void {
                        $record->update([
                            'status' => $data['status'],
                        ]);

                        $record->evaluations()->create([
                            'sae_id' => $record->sae_id,
                            'evaluator_id' => auth()->id(),
                            'score' => $data['score'] ?? null,
                            'feedback' => $data['feedback'],
                            'status' => $data['status'],
                            'evaluated_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Évaluation enregistrée avec succès')
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
