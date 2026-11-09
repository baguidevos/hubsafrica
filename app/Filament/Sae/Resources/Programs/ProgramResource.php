<?php

namespace App\Filament\Sae\Resources\Programs;

use App\Enums\ApplicationStatus;
use App\Filament\Sae\Clusters\ProgrammesCluster;
use App\Filament\Sae\Resources\Programs\Pages\CreateProgram;
use App\Filament\Sae\Resources\Programs\Pages\EditProgram;
use App\Filament\Sae\Resources\Programs\Pages\ListPrograms;
use App\Filament\Sae\Resources\Programs\Pages\ViewProgram;
use App\Filament\Sae\Resources\Programs\RelationManagers\ApplicationsRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\CohortsRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\EvaluationCriteriaRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\PartnersRelationManager;
use App\Filament\Sae\Resources\Programs\RelationManagers\PhasesRelationManager;
use App\Filament\Sae\Resources\Programs\Schemas\ProgramForm;
use App\Filament\Sae\Resources\Programs\Tables\ProgramsTable;
use App\Models\Program;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $cluster = ProgrammesCluster::class;

    protected static ?string $navigationLabel = 'Programmes';

    protected static ?string $modelLabel = 'Programme';

    protected static ?string $pluralModelLabel = 'Programmes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ProgramForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgramsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Cadrage du Programme')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Grid::make(4)->schema([
                            ImageEntry::make('image')
                                ->label('Visuel')
                                ->disk('public')
                                ->defaultImageUrl(fn (Program $program): ?string => $program->sae?->logo_url)
                                ->circular()
                                ->imageSize(72),

                            TextEntry::make('name')
                                ->label('Nom du programme')
                                ->weight('bold')
                                ->size('lg')
                                ->columnSpan(2),

                            TextEntry::make('status')
                                ->label('Statut')
                                ->badge(),

                            TextEntry::make('level.name')
                                ->label('Palier / Niveau ciblé')
                                ->badge()
                                ->color('primary')
                                ->placeholder('Transversal'),

                            TextEntry::make('target')
                                ->label('Public / Profil cible')
                                ->placeholder('Non spécifié'),

                            TextEntry::make('capacity')
                                ->label('Capacité d\'accueil')
                                ->suffix(' startups')
                                ->placeholder('Illimitée'),

                            TextEntry::make('admission_threshold_display')
                                ->label('Seuils Jury (Admission / Attente)')
                                ->badge()
                                ->color('info')
                                ->getStateUsing(fn (Program $program): string => "Admis ≥ {$program->getAdmissionThreshold()}/100 • Attente ≥ {$program->getWaitlistThreshold()}/100"),
                        ]),
                    ]),

                Section::make('Calendrier & Indicateurs Clés')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('registration_start')
                                ->label('Ouverture des candidatures')
                                ->date('d/m/Y')
                                ->placeholder('Non définie'),

                            TextEntry::make('registration_end')
                                ->label('Clôture des candidatures')
                                ->date('d/m/Y')
                                ->placeholder('Non définie'),

                            TextEntry::make('start_date')
                                ->label('Lancement du programme')
                                ->date('d/m/Y')
                                ->placeholder('Non défini'),

                            TextEntry::make('end_date')
                                ->label('Clôture prévisionnelle')
                                ->date('d/m/Y')
                                ->placeholder('Non définie'),

                            TextEntry::make('cohorts_count_metric')
                                ->label('Cohortes / Promotions')
                                ->badge()
                                ->color('info')
                                ->getStateUsing(fn (Program $program): string => ($program->cohorts_count ?? $program->cohorts()->count()).' cohorte(s)'),

                            TextEntry::make('applications_count_metric')
                                ->label('Total Candidatures')
                                ->badge()
                                ->color('primary')
                                ->getStateUsing(fn (Program $program): string => ($program->applications_count ?? $program->applications()->count()).' dossier(s)'),

                            TextEntry::make('accepted_applications_metric')
                                ->label('Candidatures retenues')
                                ->badge()
                                ->color('success')
                                ->getStateUsing(fn (Program $program): string => $program->applications()->where('status', ApplicationStatus::ACCEPTEE)->count().' retenue(s)'),

                            TextEntry::make('pending_applications_metric')
                                ->label('Nouvelles à instruire')
                                ->badge()
                                ->color('warning')
                                ->getStateUsing(fn (Program $program): string => $program->applications()->where('status', ApplicationStatus::SOUMISE)->count().' en attente'),
                        ]),
                    ]),

                Section::make('Présentation, Objectifs & Critères d\'éligibilité')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('description')
                            ->label('Description générale')
                            ->placeholder('Aucune description renseignée.')
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextEntry::make('objectives')
                                ->label('Objectifs & Livrables visés')
                                ->placeholder('Non renseignés.'),

                            TextEntry::make('eligibility')
                                ->label('Critères d\'éligibilité')
                                ->placeholder('Non renseignés.'),
                        ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CohortsRelationManager::class,
            ApplicationsRelationManager::class,
            PhasesRelationManager::class,
            EvaluationCriteriaRelationManager::class,
            PartnersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrograms::route('/'),
            'create' => CreateProgram::route('/create'),
            'view' => ViewProgram::route('/{record}'),
            'edit' => EditProgram::route('/{record}/edit'),
        ];
    }
}
