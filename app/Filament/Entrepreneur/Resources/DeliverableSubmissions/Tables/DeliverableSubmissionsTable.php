<?php

namespace App\Filament\Entrepreneur\Resources\DeliverableSubmissions\Tables;

use App\Enums\DeliverableSubmissionStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label('Livrable')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('project.name')
                    ->label('Projet')
                    ->searchable(),

                TextColumn::make('cohortPhase.name')
                    ->label('Jalon / Phase')
                    ->badge()
                    ->color('info')
                    ->placeholder('Général')
                    ->sortable(),

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

                TextColumn::make('latestEvaluation.score')
                    ->label('Note Coach')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state >= 75 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'En attente')
                    ->alignCenter(),

                TextColumn::make('submitted_at')
                    ->label('Déposé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(DeliverableSubmissionStatus::class),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Mon fichier')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn ($record) => $record->file_path ? asset('storage/'.$record->file_path) : null)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => filled($record->file_path)),

                ViewAction::make()->slideOver(),
                EditAction::make()->slideOver()
                    ->visible(fn ($record) => $record->status !== DeliverableSubmissionStatus::VALIDE),
            ])
            ->toolbarActions([
                //
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
