<?php

namespace App\Filament\Sae\Resources\Programs\Tables;

use App\Enums\ProgramStatus;
use App\Services\AccompanimentTemplateService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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

                TextColumn::make('target')
                    ->label('Cible')
                    ->searchable()
                    ->default('—'),

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
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ProgramStatus::class),

                SelectFilter::make('level_id')
                    ->label('Niveau')
                    ->relationship('level', 'name'),
            ])
            ->recordActions([
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
                            ->default('startup-loop-africinnov')
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
                ViewAction::make()->slideOver(),
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
