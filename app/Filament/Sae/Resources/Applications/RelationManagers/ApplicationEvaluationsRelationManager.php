<?php

namespace App\Filament\Sae\Resources\Applications\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationEvaluationsRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluations';

    protected static ?string $title = 'Évaluations & Notes du Jury';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-academic-cap';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('evaluator.name')
                    ->label('Membre du Jury')
                    ->weight('bold')
                    ->description(fn ($record) => $record->evaluator?->email)
                    ->searchable(),

                TextColumn::make('total_score')
                    ->label('Score calculé')
                    ->suffix(' / 100')
                    ->weight('bold')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 75 => 'success',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->alignCenter(),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge(),

                TextColumn::make('comment')
                    ->label('Avis synthétique du jury')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->comment)
                    ->default('Aucun commentaire'),

                TextColumn::make('submitted_at')
                    ->label('Évalué le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()->slideOver(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
