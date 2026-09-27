<?php

namespace App\Filament\Sae\Resources\EvaluationForms\RelationManagers;

use App\Enums\FormUnlockStatus;
use App\Models\Cohort;
use App\Models\Entrepreneur;
use App\Models\Form;
use App\Models\FormUnlock;
use App\Models\TimelineEvent;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnlocksRelationManager extends RelationManager
{
    protected static string $relationship = 'unlocks';

    protected static ?string $title = 'Entrepreneurs assignés & Suivi des réponses';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-user-group';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('entrepreneur.full_name')
                    ->label('Entrepreneur')
                    ->searchable(['first_name', 'last_name'])
                    ->weight('bold'),
                TextColumn::make('entrepreneur.email')
                    ->label('Email')
                    ->copyable()
                    ->color('gray'),
                TextColumn::make('due_date')
                    ->label('Date limite')
                    ->date('d/m/Y')
                    ->color(fn (FormUnlock $record): string => $record->isOverdue() ? 'danger' : 'gray')
                    ->description(fn (FormUnlock $record): ?string => $record->isOverdue() ? 'En retard !' : null)
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),
                TextColumn::make('completed_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Non soumis')
                    ->sortable(),
                TextColumn::make('note')
                    ->label('Consigne')
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Action::make('assign')
                    ->label('Assigner à des entrepreneurs')
                    ->icon('heroicon-o-plus-circle')
                    ->modalHeading('Assigner ce questionnaire')
                    ->modalDescription('Sélectionnez une cohorte ou des entrepreneurs. Ils recevront une notification et le questionnaire apparaîtra dans leur espace.')
                    ->modalSubmitActionLabel('Assigner & Notifier')
                    ->form([
                        Radio::make('mode')
                            ->label("Mode d'assignation")
                            ->options([
                                'cohort' => 'Toute une cohorte',
                                'custom' => 'Sélection individuelle d\'entrepreneurs',
                            ])
                            ->default('cohort')
                            ->live(),
                        Select::make('cohort_id')
                            ->label('Cohorte cible')
                            ->options(fn () => Cohort::query()->pluck('name', 'id'))
                            ->visible(fn ($get) => $get('mode') === 'cohort')
                            ->required(fn ($get) => $get('mode') === 'cohort')
                            ->searchable()
                            ->preload(),
                        Select::make('entrepreneurs')
                            ->label('Entrepreneurs')
                            ->options(function () {
                                return Entrepreneur::query()->get()->mapWithKeys(function ($e) {
                                    return [$e->id => "{$e->full_name} ({$e->email})"];
                                });
                            })
                            ->multiple()
                            ->visible(fn ($get) => $get('mode') === 'custom')
                            ->required(fn ($get) => $get('mode') === 'custom')
                            ->searchable(),
                        DatePicker::make('due_date')
                            ->label('Date limite de réponse')
                            ->default(now()->addDays(14))
                            ->required(),
                        Textarea::make('note')
                            ->label('Consignes particulières (optionnel)')
                            ->placeholder('Ex: Merci de remplir cette évaluation avant notre prochaine session de coaching.'),
                    ])
                    ->action(function (array $data): void {
                        /** @var Form $form */
                        $form = $this->getOwnerRecord();

                        $entrepreneurIds = collect();

                        if ($data['mode'] === 'cohort') {
                            $cohort = Cohort::find($data['cohort_id']);
                            if ($cohort) {
                                $entrepreneurIds = $cohort->enrollments()->pluck('entrepreneur_id')->filter()->unique();
                            }
                        } else {
                            $entrepreneurIds = collect($data['entrepreneurs'])->filter();
                        }

                        if ($entrepreneurIds->isEmpty()) {
                            Notification::make()
                                ->title('Aucun entrepreneur sélectionné')
                                ->warning()
                                ->send();

                            return;
                        }

                        $count = 0;
                        $saeId = $form->getAttribute('sae_id') ?? auth()->user()?->sae_id;

                        foreach ($entrepreneurIds as $entId) {
                            $unlock = FormUnlock::updateOrCreate(
                                [
                                    'form_id' => $form->getKey(),
                                    'entrepreneur_id' => $entId,
                                ],
                                [
                                    'sae_id' => $saeId,
                                    'unlocked_by_id' => auth()->id(),
                                    'due_date' => $data['due_date'],
                                    'note' => $data['note'] ?? null,
                                    'status' => FormUnlockStatus::EN_ATTENTE,
                                ]
                            );

                            $entrepreneur = Entrepreneur::with('user')->find($entId);

                            if ($entrepreneur?->user) {
                                Notification::make()
                                    ->title('Nouveau formulaire à remplir : ' . $form->name)
                                    ->body('Date limite : ' . \Carbon\Carbon::parse($data['due_date'])->format('d/m/Y') . ($data['note'] ? " — Consigne : {$data['note']}" : ''))
                                    ->icon('heroicon-o-clipboard-document-check')
                                    ->warning()
                                    ->sendToDatabase($entrepreneur->user);
                            }

                            if ($entrepreneur) {
                                TimelineEvent::create([
                                    'sae_id' => $saeId,
                                    'entrepreneur_id' => $entrepreneur->id,
                                    'title' => 'Questionnaire assigné : ' . $form->name,
                                    'description' => "Le questionnaire '{$form->name}' a été assigné par l'équipe d'accompagnement.",
                                    'type' => 'EVALUATION',
                                    'event_date' => now(),
                                ]);
                            }

                            $count++;
                        }

                        Notification::make()
                            ->title("{$count} entrepreneur(s) assigné(s) avec succès")
                            ->body('Les entrepreneurs ont reçu une notification dans leur espace.')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('remind')
                    ->label('Relancer')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Envoyer une relance')
                    ->modalDescription(fn (FormUnlock $record) => "Envoyer un rappel immédiat à {$record->entrepreneur?->full_name} pour compléter ce formulaire ?")
                    ->visible(fn (FormUnlock $record): bool => ! $record->isCompleted())
                    ->action(function (FormUnlock $record): void {
                        $user = $record->entrepreneur?->user;
                        if ($user) {
                            Notification::make()
                                ->title('Rappel : Questionnaire en attente')
                                ->body("N'oubliez pas de compléter le questionnaire '{$record->form?->name}'" . ($record->due_date ? " avant le {$record->due_date->format('d/m/Y')}" : ''))
                                ->danger()
                                ->icon('heroicon-o-exclamation-triangle')
                                ->sendToDatabase($user);

                            Notification::make()
                                ->title('Relance envoyée avec succès')
                                ->success()
                                ->send();
                        }
                    }),
                Action::make('view_submission')
                    ->label('Voir les réponses')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (FormUnlock $record) => "Réponses de {$record->entrepreneur?->full_name}")
                    ->visible(fn (FormUnlock $record): bool => $record->isCompleted() && $record->submission_id !== null)
                    ->infolist([
                        KeyValueEntry::make('submission.data')
                            ->label('Données soumises')
                            ->keyLabel('Question / Champ')
                            ->valueLabel('Réponse fournie'),
                    ]),
                DeleteAction::make()
                    ->label('Supprimer')
                    ->modalHeading("Supprimer l'assignation"),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
