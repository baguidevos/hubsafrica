<?php

namespace App\Filament\Sae\Resources\CoachingSessions;

use App\Filament\Sae\Clusters\AccompagnementCluster;
use App\Filament\Sae\Resources\CoachingSessions\Pages\CreateCoachingSession;
use App\Filament\Sae\Resources\CoachingSessions\Pages\EditCoachingSession;
use App\Filament\Sae\Resources\CoachingSessions\Pages\ListCoachingSessions;
use App\Filament\Sae\Resources\CoachingSessions\Pages\ViewCoachingSession;
use App\Filament\Sae\Resources\CoachingSessions\RelationManagers\AttendeesRelationManager;
use App\Filament\Sae\Resources\CoachingSessions\Schemas\CoachingSessionForm;
use App\Filament\Sae\Resources\CoachingSessions\Tables\CoachingSessionsTable;
use App\Models\CoachingSession;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class CoachingSessionResource extends Resource
{
    protected static ?string $model = CoachingSession::class;

    protected static ?string $cluster = AccompagnementCluster::class;

    protected static ?string $navigationLabel = 'Séances & Ateliers';

    protected static ?string $modelLabel = 'Séance d\'accompagnement';

    protected static ?string $pluralModelLabel = 'Séances & Ateliers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CoachingSessionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoachingSessionsTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cadrage de la séance')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('coach.name')
                                ->label('Coach référent')
                                ->weight('bold'),
                            TextEntry::make('project.name')
                                ->label('Projet')
                                ->placeholder('Session collective'),
                            TextEntry::make('entrepreneur.full_name')
                                ->label('Entrepreneur')
                                ->placeholder('Session collective'),
                            TextEntry::make('cohort.name')
                                ->label('Cohorte ciblée')
                                ->placeholder('—'),
                            TextEntry::make('cohortPhase.name')
                                ->label('Jalon pédagogique')
                                ->placeholder('—'),
                            TextEntry::make('date')
                                ->label('Date & heure')
                                ->dateTime('d/m/Y à H:i'),
                            TextEntry::make('duration')
                                ->label('Durée')
                                ->suffix(' min'),
                            TextEntry::make('type')
                                ->label('Type d\'accompagnement')
                                ->badge(),
                            TextEntry::make('status')
                                ->label('Statut')
                                ->badge(),
                            TextEntry::make('theme')
                                ->label('Thème')
                                ->columnSpan(2),
                        ]),
                    ]),

                Section::make('Contenu pédagogique & Livrables visés')
                    ->schema([
                        TextEntry::make('objectives')
                            ->label('Objectifs fixés')
                            ->placeholder('Non renseigné'),
                        TextEntry::make('report')
                            ->label('Compte-rendu de la séance')
                            ->placeholder('Non rédigé'),
                        TextEntry::make('next_actions')
                            ->label('Prochaines actions convenues (Roadmap)')
                            ->placeholder('Aucune action immédiate'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttendeesRelationManager::class,
            RelationManagers\SessionDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoachingSessions::route('/'),
            'create' => CreateCoachingSession::route('/create'),
            'view' => ViewCoachingSession::route('/{record}'),
            'edit' => EditCoachingSession::route('/{record}/edit'),
        ];
    }
}
