<?php

namespace App\Filament\Sae\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Applications\Pages\CreateApplication;
use App\Filament\Sae\Resources\Applications\Pages\EditApplication;
use App\Filament\Sae\Resources\Applications\Pages\KanbanApplications;
use App\Filament\Sae\Resources\Applications\Pages\ListApplications;
use App\Filament\Sae\Resources\Applications\Pages\ViewApplication;
use App\Filament\Sae\Resources\Applications\RelationManagers\ApplicationEvaluationsRelationManager;
use App\Filament\Sae\Resources\Applications\RelationManagers\DocumentsRelationManager;
use App\Filament\Sae\Resources\Applications\Schemas\ApplicationForm;
use App\Filament\Sae\Resources\Applications\Tables\ApplicationsTable;
use App\Models\Application;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Candidatures & Sélection';

    protected static ?string $modelLabel = 'Candidature';

    protected static ?string $pluralModelLabel = 'Candidatures & Sélection';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $tenant = Filament::getTenant();

        $count = static::getModel()::query()
            ->when($tenant, fn ($query) => $query->where('sae_id', $tenant->id))
            ->where('status', ApplicationStatus::SOUMISE)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Nouvelles candidatures non traitées';
    }

    public static function form(Schema $schema): Schema
    {
        return ApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApplicationsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidat & Projet')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('entrepreneur.full_name')
                                ->label('Entrepreneur candidat')
                                ->weight('bold'),
                            TextEntry::make('entrepreneur.email')
                                ->label('Email'),
                            TextEntry::make('entrepreneur.phone')
                                ->label('Téléphone')
                                ->placeholder('—'),
                            TextEntry::make('project.name')
                                ->label('Projet associé')
                                ->weight('bold')
                                ->placeholder('—'),
                            TextEntry::make('project.sector')
                                ->label('Secteur')
                                ->badge()
                                ->color('primary')
                                ->placeholder('—'),
                            TextEntry::make('project.team_size')
                                ->label('Équipe')
                                ->suffix(' personne(s)')
                                ->placeholder('—'),
                        ]),
                    ]),

                Section::make('Affectation & Délibération')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('program.name')
                                ->label('Programme d\'accompagnement'),
                            TextEntry::make('cohort.name')
                                ->label('Cohorte / Promotion')
                                ->badge()
                                ->color('info')
                                ->placeholder('Non assignée'),
                            TextEntry::make('submitted_at')
                                ->label('Date de soumission')
                                ->dateTime('d/m/Y H:i'),
                            TextEntry::make('status')
                                ->label('Statut actuel')
                                ->badge(),
                            TextEntry::make('score')
                                ->label('Note Jury')
                                ->badge()
                                ->color(fn ($state) => match (true) {
                                    $state === null => 'gray',
                                    $state >= 75 => 'success',
                                    $state >= 50 => 'warning',
                                    default => 'danger',
                                })
                                ->formatStateUsing(fn ($state) => $state !== null ? "{$state} / 100" : 'Non notée'),
                            TextEntry::make('decision')
                                ->label('Intitulé décision')
                                ->placeholder('En cours d\'instruction'),
                        ]),
                    ]),

                Section::make('Motivation & Commentaires')
                    ->schema([
                        TextEntry::make('comment')
                            ->label('Motivation & Remarques')
                            ->columnSpanFull()
                            ->placeholder('Aucun commentaire'),
                        TextEntry::make('rejection_reason')
                            ->label('Motif de refus')
                            ->columnSpanFull()
                            ->visible(fn (?Application $record) => ! empty($record?->rejection_reason))
                            ->color('danger'),
                    ]),

                Section::make('Réponses au questionnaire spécifique')
                    ->schema([
                        KeyValueEntry::make('answers')
                            ->label('Questions & Réponses')
                            ->keyLabel('Question')
                            ->valueLabel('Réponse'),
                    ])
                    ->visible(fn (?Application $record) => ! empty($record?->answers))
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ApplicationEvaluationsRelationManager::class,
            DocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApplications::route('/'),
            'kanban' => KanbanApplications::route('/kanban'),
            'create' => CreateApplication::route('/create'),
            'view' => ViewApplication::route('/{record}'),
            'edit' => EditApplication::route('/{record}/edit'),
        ];
    }
}
