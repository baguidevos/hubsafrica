<?php

namespace App\Filament\Sae\Resources\Entrepreneurs;

use App\Filament\Sae\Resources\Entrepreneurs\Pages\CreateEntrepreneur;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\EditEntrepreneur;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Filament\Sae\Resources\Entrepreneurs\Pages\ViewEntrepreneur;
use App\Filament\Sae\Resources\Entrepreneurs\RelationManagers\ApplicationsRelationManager;
use App\Filament\Sae\Resources\Entrepreneurs\RelationManagers\CoachingSessionsRelationManager;
use App\Filament\Sae\Resources\Entrepreneurs\RelationManagers\ProjectsRelationManager;
use App\Filament\Sae\Resources\Entrepreneurs\Schemas\EntrepreneurForm;
use App\Filament\Sae\Resources\Entrepreneurs\Tables\EntrepreneursTable;
use App\Models\Entrepreneur;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EntrepreneurResource extends Resource
{
    protected static ?string $model = Entrepreneur::class;

    protected static string | \UnitEnum | null $navigationGroup = 'Portefeuille';

    protected static ?string $navigationLabel = 'Entrepreneurs';

    protected static ?string $modelLabel = 'Entrepreneur';

    protected static ?string $pluralModelLabel = 'Entrepreneurs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return EntrepreneurForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EntrepreneursTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Contact')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('full_name')
                                ->label('Nom complet')
                                ->weight('bold'),
                            TextEntry::make('email')
                                ->label('Email'),
                            TextEntry::make('phone')
                                ->label('Téléphone'),
                            TextEntry::make('status')
                                ->label('Statut du dossier')
                                ->badge()
                                ->colors([
                                    'success' => 'ACTIVE',
                                    'danger' => 'ABANDONNE',
                                    'emerald' => 'DIPLOME',
                                    'warning' => 'SUSPENDU',
                                ]),
                            TextEntry::make('created_at')
                                ->label('Date d\'inscription')
                                ->date('d/m/Y'),
                        ]),
                    ]),

                Section::make('Profil socio-démographique & Inclusion (CUBE)')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('gender')
                                ->label('Genre')
                                ->formatStateUsing(fn ($state) => match ($state) {
                                    'F' => 'Féminin',
                                    'M' => 'Masculin',
                                    default => $state ?: '—',
                                })
                                ->badge(),
                            TextEntry::make('birth_date')
                                ->label('Date de naissance')
                                ->date('d/m/Y'),
                            TextEntry::make('territory')
                                ->label('Territoire / Région'),
                            TextEntry::make('country')
                                ->label('Pays d\'origine'),
                            TextEntry::make('education_level')
                                ->label('Niveau d\'études'),
                            TextEntry::make('professional_status')
                                ->label('Situation professionnelle'),
                            TextEntry::make('profession')
                                ->label('Profession antérieure'),
                            TextEntry::make('legal_status')
                                ->label('Statut juridique'),
                        ]),
                    ]),

                Section::make('Notes de cadrage & observations')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Observations de l\'incubateur')
                            ->columnSpanFull()
                            ->placeholder('Aucune observation particulière.'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProjectsRelationManager::class,
            ApplicationsRelationManager::class,
            CoachingSessionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEntrepreneurs::route('/'),
            'create' => CreateEntrepreneur::route('/create'),
            'view' => ViewEntrepreneur::route('/{record}'),
            'edit' => EditEntrepreneur::route('/{record}/edit'),
        ];
    }
}
