<?php

namespace App\Filament\Sae\Resources\Entrepreneurs\Tables;

use App\Enums\CohortStatus;
use App\Enums\EntrepreneurStatus;
use App\Enums\Gender;
use App\Models\Cohort;
use App\Models\Enrollment;
use App\Models\Entrepreneur;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EntrepreneursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Entrepreneur')
                    ->weight('bold')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('cohorts.name')
                    ->label('Cohorte(s)')
                    ->badge()
                    ->color('info')
                    ->placeholder('Hors cohorte')
                    ->separator(', '),

                TextColumn::make('projects.name')
                    ->label('Projet(s)')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Aucun projet')
                    ->limit(25)
                    ->separator(', '),

                TextColumn::make('gender')
                    ->label('Genre')
                    ->badge()
                    ->sortable(),

                TextColumn::make('territory')
                    ->label('Territoire')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('education_level')
                    ->label('Niveau d\'études')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('professional_status')
                    ->label('Situation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Date d\'inscription')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('cohorts')
                    ->label('Cohorte')
                    ->relationship('cohorts', 'name'),

                SelectFilter::make('gender')
                    ->label('Genre')
                    ->options(Gender::class),

                SelectFilter::make('status')
                    ->label('Statut du dossier')
                    ->options(EntrepreneurStatus::class),

                SelectFilter::make('education_level')
                    ->label('Niveau d\'études')
                    ->options([
                        'Sans diplôme' => 'Sans diplôme',
                        'Bac' => 'Baccalauréat',
                        'Bac+2' => 'Bac+2',
                        'Licence' => 'Licence',
                        'Master' => 'Master',
                        'Doctorat' => 'Doctorat',
                    ]),
            ])
            ->recordActions([
                Action::make('assign_cohort')
                    ->label('Rattacher à une cohorte')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->modalHeading('Rattacher l\'entrepreneur à une cohorte en cours')
                    ->modalDescription(fn (Entrepreneur $record) => "Intégrer directement {$record->full_name} à un programme/cohorte d'accompagnement.")
                    ->schema([
                        Select::make('cohort_id')
                            ->label('Cohorte cible')
                            ->options(function () {
                                $tenant = Filament::getTenant();

                                return Cohort::query()
                                    ->when($tenant, fn ($q) => $q->where('sae_id', $tenant->id))
                                    ->whereIn('status', [CohortStatus::OUVERTE, CohortStatus::EN_COURS])
                                    ->with('program')
                                    ->get()
                                    ->mapWithKeys(fn (Cohort $c) => [
                                        $c->id => "{$c->name}".($c->program ? " ({$c->program->name})" : ''),
                                    ]);
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('project_id')
                            ->label('Projet associé (Optionnel)')
                            ->options(fn (Entrepreneur $record) => $record->projects()->pluck('name', 'id'))
                            ->placeholder('Sélectionner un projet ou laisser vide')
                            ->searchable(),

                        Select::make('status')
                            ->label('Statut dans la cohorte')
                            ->options([
                                'ACTIF' => 'Actif',
                                'TERMINE' => 'Terminé',
                                'ABANDON' => 'Abandon',
                            ])
                            ->default('ACTIF')
                            ->required(),
                    ])
                    ->action(function (Entrepreneur $record, array $data) {
                        $tenant = Filament::getTenant();
                        $saeId = $tenant?->id ?? $record->sae_id;

                        $exists = Enrollment::where('cohort_id', $data['cohort_id'])
                            ->where('entrepreneur_id', $record->id)
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Déjà inscrit')
                                ->body('Cet entrepreneur est déjà inscrit dans cette cohorte.')
                                ->warning()
                                ->send();

                            return;
                        }

                        Enrollment::create([
                            'sae_id' => $saeId,
                            'cohort_id' => $data['cohort_id'],
                            'entrepreneur_id' => $record->id,
                            'project_id' => $data['project_id'] ?? null,
                            'status' => $data['status'] ?? 'ACTIF',
                            'enrolled_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Rattachement effectué')
                            ->body("L'entrepreneur {$record->full_name} a été rattaché avec succès à la cohorte.")
                            ->success()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
