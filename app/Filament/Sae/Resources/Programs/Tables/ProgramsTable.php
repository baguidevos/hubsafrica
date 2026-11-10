<?php

namespace App\Filament\Sae\Resources\Programs\Tables;

use App\Enums\ProgramStatus;
use App\Models\Program;
use App\Services\AccompanimentTemplateService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Visuel')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => $record->sae?->logo_url)
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Programme')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('level.name')
                    ->label('Niveau')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->default('Transversal'),

                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Actif (Front)')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('cohorts_count')
                    ->counts('cohorts')
                    ->label('Cohortes')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('applications_count')
                    ->counts('applications')
                    ->label('Candidatures')
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('capacity')
                    ->label('Capacité')
                    ->suffix(' startups')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('registration_end')
                    ->label('Clôture dépôts')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('start_date')
                    ->label('Démarrage')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Visibilité portail public')
                    ->placeholder('Tous les programmes')
                    ->trueLabel('Actifs (visibles sur le front)')
                    ->falseLabel('Désactivés (masqués)'),

                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ProgramStatus::class),

                SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (Program $record): string => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Program $record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn (Program $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Program $record): string => $record->is_active ? "Désactiver « {$record->name} »" : "Activer « {$record->name} »")
                    ->modalDescription(fn (Program $record): string => $record->is_active
                        ? 'Ce programme sera masqué sur le portail public, mais restera intégralement accessible dans votre espace de gestion SAE.'
                        : 'Ce programme sera de nouveau visible pour les entrepreneurs sur le portail public.')
                    ->action(function (Program $record): void {
                        $newState = ! $record->is_active;
                        $record->update(['is_active' => $newState]);

                        Notification::make()
                            ->title($newState ? 'Programme activé' : 'Programme désactivé')
                            ->body($newState
                                ? "Le programme « {$record->name} » est désormais visible sur le portail public."
                                : "Le programme « {$record->name} » a été masqué du portail public.")
                            ->success()
                            ->send();
                    }),
                Action::make('applyTemplate')
                    ->label('Template')
                    ->icon('heroicon-o-sparkles')
                    ->color('emerald')
                    ->tooltip('Appliquer un pack méthodologique complet (6 phases, livrables, etc.)')
                    ->slideOver()
                    ->modalHeading(fn ($record) => "Appliquer un modèle à : {$record->name}")
                    ->form([
                        Select::make('template_key')
                            ->label('Modèle méthodologique')
                            ->options(fn (AccompanimentTemplateService $service) => $service->asSelectOptions())
                            ->default('cube-saei-kpital')
                            ->required(),
                    ])
                    ->action(function ($record, array $data, AccompanimentTemplateService $service) {
                        $sae = filament()->getTenant();
                        $summary = $service->apply($data['template_key'], $sae, $record);

                        Notification::make()
                            ->title('Modèle appliqué !')
                            ->success()
                            ->body("Généré pour {$record->name} : {$summary['phases']} phases, {$summary['startup_deliverables']} livrables startups, {$summary['programme_deliverables']} livrables SAEI.")
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
