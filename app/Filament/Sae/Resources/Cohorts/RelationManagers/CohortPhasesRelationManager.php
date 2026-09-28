<?php

namespace App\Filament\Sae\Resources\Cohorts\RelationManagers;

use App\Enums\ProgramPhaseStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CohortPhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'phases';

    protected static ?string $title = 'Jalons réels & Calendrier de la cohorte';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-calendar-days';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom du jalon')
                    ->required()
                    ->columnSpanFull(),

                Grid::make(3)->schema([
                    DatePicker::make('starts_on')
                        ->label('Date d\'ouverture / début')
                        ->helperText('Début effectif pour les startups de la cohorte'),

                    DatePicker::make('ends_on')
                        ->label('Date d\'échéance / fin')
                        ->helperText('Fin de la phase et remise des livrables'),

                    Select::make('status')
                        ->label('Statut du jalon')
                        ->options(ProgramPhaseStatus::class)
                        ->default(ProgramPhaseStatus::A_VENIR)
                        ->required(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Jalon pédagogique')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('starts_on')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->default('Non planifié')
                    ->sortable(),

                TextColumn::make('ends_on')
                    ->label('Échéance')
                    ->date('d/m/Y')
                    ->default('Non planifiée')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('État')
                    ->badge(),
            ])
            ->headerActions([
                Action::make('syncFromProgram')
                    ->label('Synchroniser depuis le programme')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Synchronisation des phases du programme')
                    ->modalDescription('Cette action va copier les jalons définis dans le programme parent et calculer automatiquement les dates prévisionnelles.')
                    ->action(function () {
                        $cohort = $this->getOwnerRecord();
                        $cohort->syncPhasesFromProgram();

                        Notification::make()
                            ->title('Phases synchronisées avec succès !')
                            ->body("Les jalons du programme ont été instanciés pour la cohorte {$cohort->name}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ajuster')
                    ->slideOver(),
            ])
            ->defaultSort('position', 'asc');
    }
}
