<?php

namespace App\Filament\Sae\Resources\FormAttempts\Tables;

use App\Enums\FormAttemptStatus;
use App\Models\FormAttempt;
use App\Services\FormAttemptTracker;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormAttemptsTable
{
    public static function configure(Table $table): Table
    {
        $tracker = app(FormAttemptTracker::class);

        return $table
            ->deferLoading()
            ->defaultSort('last_activity_at', 'desc')
            ->columns([
                TextColumn::make('last_activity_at')
                    ->label('Dernière activité')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Candidat / Utilisateur')
                    ->weight('bold')
                    ->description(fn (FormAttempt $record): string => $record->entrepreneur?->email ?: ($record->user?->email ?? '—'))
                    ->searchable(['user.name', 'user.email']),

                TextColumn::make('form.name')
                    ->label('Formulaire')
                    ->limit(28)
                    ->tooltip(fn (FormAttempt $record): ?string => $record->form?->name)
                    ->searchable(),

                TextColumn::make('cohort.name')
                    ->label('Cohorte')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('progress')
                    ->label('Progression')
                    ->state(fn (FormAttempt $record): string => "{$record->filled_fields_count}/{$record->total_fields_count} champs")
                    ->description(fn (FormAttempt $record): ?string => $record->last_step_label)
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('inferred_reason')
                    ->label('Raison / Blocage')
                    ->state(fn (FormAttempt $record): string => $tracker->inferReason($record))
                    ->limit(60)
                    ->tooltip(fn (FormAttempt $record): string => $tracker->inferReason($record)),
            ])
            ->filters([
                Filter::make('incomplete_only')
                    ->label('Non finalisés uniquement')
                    ->default()
                    ->query(fn (Builder $query): Builder => $query->where('status', '!=', FormAttemptStatus::SOUMIS->value)),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(FormAttemptStatus::class),

                SelectFilter::make('cohort_id')
                    ->label('Cohorte')
                    ->relationship('cohort', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('form_id')
                    ->label('Formulaire')
                    ->relationship('form', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->slideOver()
                    ->schema([
                        Section::make('Détail de la saisie')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextEntry::make('user.name')
                                        ->label('Utilisateur')
                                        ->placeholder('—'),
                                    TextEntry::make('user.email')
                                        ->label('Email')
                                        ->placeholder('—'),
                                    TextEntry::make('entrepreneur.phone')
                                        ->label('Téléphone')
                                        ->placeholder('—'),
                                    TextEntry::make('status')
                                        ->label('Statut')
                                        ->badge(),
                                    TextEntry::make('form.name')
                                        ->label('Formulaire')
                                        ->placeholder('—'),
                                    TextEntry::make('cohort.name')
                                        ->label('Cohorte')
                                        ->placeholder('—'),
                                    TextEntry::make('started_at')
                                        ->label('Ouvert le')
                                        ->dateTime('d/m/Y H:i:s'),
                                    TextEntry::make('last_activity_at')
                                        ->label('Dernière activité')
                                        ->dateTime('d/m/Y H:i:s'),
                                    TextEntry::make('last_step_label')
                                        ->label('Dernière étape atteinte')
                                        ->placeholder('—'),
                                    TextEntry::make('last_filled_field')
                                        ->label('Dernier champ renseigné')
                                        ->placeholder('—'),
                                ]),
                                TextEntry::make('inferred_reason')
                                    ->label('Diagnostic / Raison du blocage')
                                    ->state(fn (FormAttempt $record): string => $tracker->inferReason($record))
                                    ->columnSpanFull(),
                                TextEntry::make('validation_errors_list')
                                    ->label('Erreurs de validation détaillées')
                                    ->state(function (FormAttempt $record): ?string {
                                        $errors = $record->validation_errors;
                                        if (! is_array($errors) || $errors === []) {
                                            return null;
                                        }

                                        return collect($errors)
                                            ->map(fn (string $message, string $field): string => "• {$field} : {$message}")
                                            ->implode("\n");
                                    })
                                    ->placeholder('Aucune erreur de validation enregistrée')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
