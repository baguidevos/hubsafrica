<?php

namespace App\Filament\Sae\Resources\Cohorts;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Cohorts\Pages\CreateCohort;
use App\Filament\Sae\Resources\Cohorts\Pages\EditCohort;
use App\Filament\Sae\Resources\Cohorts\Pages\ListCohorts;
use App\Filament\Sae\Resources\Cohorts\Pages\ViewCohort;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortApplicationsRelationManager;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortPhasesRelationManager;
use App\Filament\Sae\Resources\Cohorts\RelationManagers\CohortSessionsRelationManager;
use App\Filament\Sae\Resources\Cohorts\Schemas\CohortForm;
use App\Filament\Sae\Resources\Cohorts\Tables\CohortsTable;
use App\Models\Cohort;
use App\Services\ProgramEvaluationPresenter;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CohortResource extends Resource
{
    protected static ?string $model = Cohort::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Cohortes & Promotions';

    protected static ?string $modelLabel = 'Cohorte';

    protected static ?string $pluralModelLabel = 'Cohortes & Promotions';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CohortForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CohortsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Cadrage de la Cohorte')
                    ->icon('heroicon-o-users')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('name')
                                ->label('Nom de la cohorte / promotion')
                                ->weight('bold')
                                ->size('lg'),

                            TextEntry::make('program.name')
                                ->label('Programme d\'accompagnement')
                                ->badge()
                                ->color('info'),

                            TextEntry::make('status')
                                ->label('Statut')
                                ->badge(),

                            TextEntry::make('applicationForm.name')
                                ->label('Formulaire de candidature')
                                ->badge()
                                ->color('primary')
                                ->placeholder('Modèle standard Hubs Africa'),

                            TextEntry::make('start_date')
                                ->label('Date de lancement')
                                ->date('d/m/Y')
                                ->placeholder('Non définie'),

                            TextEntry::make('end_date')
                                ->label('Date de clôture')
                                ->date('d/m/Y')
                                ->placeholder('Non définie'),

                            TextEntry::make('applications_count_metric')
                                ->label('Candidatures reçues')
                                ->badge()
                                ->color('primary')
                                ->getStateUsing(fn (Cohort $record): string => ($record->applications_count ?? $record->applications()->count()).' dossier(s)'),

                            TextEntry::make('occupancy_metric')
                                ->label('Startups admises / Capacité')
                                ->badge()
                                ->color(fn (Cohort $record): string => ProgramEvaluationPresenter::determineOccupancyColor(
                                    $record->enrollments_count ?? $record->enrollments()->count(),
                                    $record->capacity
                                ))
                                ->getStateUsing(fn (Cohort $record): string => ProgramEvaluationPresenter::formatOccupancyRate(
                                    $record->enrollments_count ?? $record->enrollments()->count(),
                                    $record->capacity
                                )),

                            TextEntry::make('pending_applications_metric')
                                ->label('Nouvelles à instruire')
                                ->badge()
                                ->color('warning')
                                ->getStateUsing(fn (Cohort $record): string => $record->applications()->where('status', ApplicationStatus::SOUMISE)->count().' en attente'),
                        ]),
                    ]),

                Section::make('Objectifs & Thématiques de la promotion')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('objectives')
                            ->label('Objectifs spécifiques')
                            ->placeholder('Aucun objectif spécifique renseigné.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CohortApplicationsRelationManager::class,
            CohortPhasesRelationManager::class,
            CohortSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCohorts::route('/'),
            'create' => CreateCohort::route('/create'),
            'view' => ViewCohort::route('/{record}'),
            'edit' => EditCohort::route('/{record}/edit'),
        ];
    }
}
