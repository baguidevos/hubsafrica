<?php

namespace App\Filament\Sae\Resources\Projects\RelationManagers;

use App\Models\Level;
use App\Models\Project;
use App\Models\ProjectLevelHistory;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LevelHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'levelHistories';

    protected static ?string $title = 'Parcours & Historique des Paliers';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-chart-bar';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('fromLevel.name')
                    ->label('Palier d\'origine')
                    ->badge()
                    ->color('gray')
                    ->default('Entrée initiale'),

                TextColumn::make('toLevel.name')
                    ->label('Nouveau palier')
                    ->badge()
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('started_at')
                    ->label('Date d\'entrée')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('ended_at')
                    ->label('Date de sortie')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('En cours (Actif)')
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Temps passé')
                    ->state(function (ProjectLevelHistory $record): string {
                        if (! $record->started_at) {
                            return '—';
                        }
                        $end = $record->ended_at ?? now();

                        return $record->started_at->diffForHumans($end, syntax: true);
                    })
                    ->badge()
                    ->color(fn (ProjectLevelHistory $record) => $record->ended_at ? 'gray' : 'primary'),

                TextColumn::make('validator.name')
                    ->label('Validé par')
                    ->default('Système / Délibération')
                    ->color('gray'),

                TextColumn::make('reason')
                    ->label('Motif & Contexte')
                    ->limit(40)
                    ->tooltip(fn (ProjectLevelHistory $record) => $record->reason)
                    ->placeholder('—'),
            ])
            ->headerActions([
                Action::make('promote_level')
                    ->label('Faire évoluer le palier')
                    ->icon('heroicon-o-arrow-trending-up')
                    ->color('success')
                    ->modalHeading('Faire progresser le palier d\'accompagnement')
                    ->modalDescription(function () {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();
                        $currentName = $project->currentLevel?->name ?? 'Non défini';

                        return "Projet : {$project->name} — Palier actuel : {$currentName}";
                    })
                    ->form([
                        Select::make('to_level_id')
                            ->label('Nouveau palier d\'accompagnement')
                            ->options(function () {
                                /** @var Project $project */
                                $project = $this->getOwnerRecord();

                                return Level::query()
                                    ->where('id', '!=', $project->current_level_id)
                                    ->orderBy('order')
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable(),

                        Textarea::make('reason')
                            ->label('Motif de progression / Décision')
                            ->placeholder('Ex: Atteinte des livrables du palier précédent, validation du comité...')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (array $data): void {
                        /** @var Project $project */
                        $project = $this->getOwnerRecord();
                        $newLevel = Level::find($data['to_level_id']);

                        if (! $project || ! $newLevel) {
                            return;
                        }

                        $project->update([
                            'current_level_id' => $newLevel->id,
                        ]);

                        // Mise à jour du motif dans le dernier historique créé par le hook booted
                        $lastHistory = ProjectLevelHistory::where('project_id', $project->id)->latest('id')->first();
                        if ($lastHistory && ! empty($data['reason'])) {
                            $lastHistory->update([
                                'reason' => $data['reason'],
                                'validated_by' => auth()->id(),
                            ]);
                        }

                        Notification::make()
                            ->title("Projet promu au palier « {$newLevel->name} »")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('started_at', 'desc');
    }
}
