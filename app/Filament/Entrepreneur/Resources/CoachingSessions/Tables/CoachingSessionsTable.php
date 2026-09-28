<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions\Tables;

use App\Enums\CoachingSessionStatus;
use App\Enums\CoachingSessionType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CoachingSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->label('Date & heure')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('coach.name')
                    ->label('Coach référent')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('cohort.name')
                    ->label('Cohorte / Promotion')
                    ->badge()
                    ->color('info')
                    ->placeholder('Individuel')
                    ->sortable(),

                TextColumn::make('attendance')
                    ->label('Présence')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $entrepreneur = auth()->user()?->entrepreneurProfile;
                        if (! $entrepreneur) {
                            return null;
                        }

                        $attendee = $record->attendees()->where('entrepreneur_id', $entrepreneur->id)->first();
                        if (! $attendee) {
                            return $record->isCollective() ? 'Inscrit' : null;
                        }

                        return $attendee->attended ? 'Présent' : 'Absent';
                    })
                    ->colors([
                        'success' => 'Présent',
                        'danger' => 'Absent',
                        'info' => 'Inscrit',
                    ])
                    ->placeholder('—'),

                TextColumn::make('theme')
                    ->label('Thème de la séance')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('duration')
                    ->label('Durée')
                    ->suffix(' min')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options(CoachingSessionType::class),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(CoachingSessionStatus::class),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('date', 'desc');
    }
}
