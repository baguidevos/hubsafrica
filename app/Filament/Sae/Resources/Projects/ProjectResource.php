<?php

namespace App\Filament\Sae\Resources\Projects;

use App\Filament\Sae\Clusters\PortefeuilleCluster;
use App\Filament\Sae\Resources\Applications\RelationManagers\DocumentsRelationManager;
use App\Filament\Sae\Resources\Projects\Pages\CreateProject;
use App\Filament\Sae\Resources\Projects\Pages\EditProject;
use App\Filament\Sae\Resources\Projects\Pages\ListProjects;
use App\Filament\Sae\Resources\Projects\Pages\ViewProject;
use App\Filament\Sae\Resources\Projects\RelationManagers\ApplicationsRelationManager;
use App\Filament\Sae\Resources\Projects\RelationManagers\CoachAssignmentsRelationManager;
use App\Filament\Sae\Resources\Projects\RelationManagers\CoachingSessionsRelationManager;
use App\Filament\Sae\Resources\Projects\RelationManagers\DeliverableSubmissionsRelationManager;
use App\Filament\Sae\Resources\Projects\RelationManagers\FundingRecordsRelationManager;
use App\Filament\Sae\Resources\Projects\Schemas\ProjectForm;
use App\Filament\Sae\Resources\Projects\Tables\ProjectsTable;
use App\Models\Project;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $cluster = PortefeuilleCluster::class;

    protected static ?string $navigationLabel = 'Startups & Entreprises';

    protected static ?string $modelLabel = 'Startup';

    protected static ?string $pluralModelLabel = 'Startups & Entreprises';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProjectsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Porteur du projet')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('name')
                                ->label('Nom du projet')
                                ->weight('bold'),
                            TextEntry::make('entrepreneur.full_name')
                                ->label('Porteur de projet'),
                            TextEntry::make('status')
                                ->label('Statut du dossier')
                                ->badge()
                                ->colors([
                                    'success' => 'ACTIF',
                                    'danger' => 'BLOQUE',
                                    'warning' => 'STAGNANT',
                                    'gray' => 'ABANDONNE',
                                    'emerald' => 'DIPLOME',
                                ]),
                            TextEntry::make('sector')
                                ->label('Secteur d\'activité'),
                            TextEntry::make('territory')
                                ->label('Territoire / Région'),
                            TextEntry::make('currentLevel.name')
                                ->label('Niveau / Jalon')
                                ->badge()
                                ->color('info'),
                            TextEntry::make('team_size')
                                ->label('Taille de l\'équipe')
                                ->suffix(' personne(s)'),
                            TextEntry::make('latestEnrollment.cohort.name')
                                ->label('Cohorte active')
                                ->badge()
                                ->color('info')
                                ->placeholder('Non assignée'),
                            TextEntry::make('latestApplication.score')
                                ->label('Dernière note Jury')
                                ->badge()
                                ->color(fn ($state) => match (true) {
                                    $state === null => 'gray',
                                    $state >= 75 => 'success',
                                    $state >= 50 => 'warning',
                                    default => 'danger',
                                })
                                ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non notée'),
                            TextEntry::make('created_at')
                                ->label('Date d\'intégration')
                                ->date('d/m/Y'),
                        ]),
                    ]),

                Section::make('Descriptif & Modèle économique')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('pitch')
                            ->label('Pitch')
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextEntry::make('problem')
                                ->label('Problème identifié'),
                            TextEntry::make('solution')
                                ->label('Solution apportée'),
                            TextEntry::make('product_service')
                                ->label('Produit ou service'),
                            TextEntry::make('value_proposition')
                                ->label('Proposition de valeur'),
                            TextEntry::make('target')
                                ->label('Cible clientèle'),
                            TextEntry::make('market')
                                ->label('Marché visé'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationsRelationManager::class,
            CoachingSessionsRelationManager::class,
            DeliverableSubmissionsRelationManager::class,
            FundingRecordsRelationManager::class,
            CoachAssignmentsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProjects::route('/'),
            'create' => CreateProject::route('/create'),
            'view' => ViewProject::route('/{record}'),
            'edit' => EditProject::route('/{record}/edit'),
        ];
    }
}
