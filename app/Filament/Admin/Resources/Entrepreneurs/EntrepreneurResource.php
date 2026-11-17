<?php

namespace App\Filament\Admin\Resources\Entrepreneurs;

use App\Filament\Admin\Resources\Entrepreneurs\Pages\CreateEntrepreneur;
use App\Filament\Admin\Resources\Entrepreneurs\Pages\EditEntrepreneur;
use App\Filament\Admin\Resources\Entrepreneurs\Pages\ListEntrepreneurs;
use App\Filament\Admin\Resources\Entrepreneurs\Pages\ViewEntrepreneur;
use App\Filament\Admin\Resources\Entrepreneurs\RelationManagers\ApplicationsRelationManager;
use App\Filament\Admin\Resources\Entrepreneurs\RelationManagers\ProjectsRelationManager;
use App\Filament\Admin\Resources\Entrepreneurs\Schemas\EntrepreneurForm;
use App\Filament\Admin\Resources\Entrepreneurs\Tables\EntrepreneursTable;
use App\Models\Entrepreneur;
use BackedEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EntrepreneurResource extends Resource
{
    protected static ?string $model = Entrepreneur::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Portail Entrepreneurs';

    protected static ?string $modelLabel = 'Entrepreneur';

    protected static ?string $pluralModelLabel = 'Entrepreneurs';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 3;

    public static function getRouteBaseName(?Panel $panel = null): string
    {
        $panelId = $panel?->getId() ?? 'hubs-hq';

        return "filament.{$panelId}.resources.entrepreneurs";
    }

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
                Section::make('Identité & Rattachement')
                    ->schema([
                        Grid::make(3)->schema([
                            ImageEntry::make('photo')
                                ->label('Photo')
                                ->circular()
                                ->disk('public')
                                ->defaultImageUrl(fn (Entrepreneur $record) => 'https://ui-avatars.com/api/?name='.urlencode($record->full_name).'&color=FFFFFF&background=0F766E'),

                            TextEntry::make('full_name')
                                ->label('Nom complet')
                                ->weight('bold'),

                            TextEntry::make('sae.name')
                                ->label('Structure SAE partenaire')
                                ->badge()
                                ->color('slate')
                                ->placeholder('Aucune'),

                            TextEntry::make('email')
                                ->label('Email'),

                            TextEntry::make('phone')
                                ->label('Téléphone'),

                            TextEntry::make('status')
                                ->label('Statut du dossier')
                                ->badge(),

                            TextEntry::make('created_at')
                                ->label('Date d\'inscription')
                                ->date('d/m/Y'),

                            TextEntry::make('projects_count')
                                ->state(fn (Entrepreneur $record): int => $record->projects()->count())
                                ->label('Nombre de projets')
                                ->badge()
                                ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                                ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} projet(s) renseigné(s)" : 'Aucun projet'),
                        ]),
                    ]),

                Section::make('Profil socio-démographique & Territoire')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('gender')
                                ->label('Genre')
                                ->badge(),

                            TextEntry::make('birth_date')
                                ->label('Date de naissance')
                                ->date('d/m/Y')
                                ->placeholder('—'),

                            TextEntry::make('country')
                                ->label('Pays d\'origine'),

                            TextEntry::make('territory')
                                ->label('Territoire / Région')
                                ->placeholder('—'),

                            TextEntry::make('education_level')
                                ->label('Niveau d\'études')
                                ->placeholder('—'),

                            TextEntry::make('professional_status')
                                ->label('Situation professionnelle')
                                ->placeholder('—'),

                            TextEntry::make('profession')
                                ->label('Métier / Profession')
                                ->placeholder('—'),
                        ]),
                    ]),

                Section::make('Observations')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('notes')
                            ->label('Notes de suivi')
                            ->columnSpanFull()
                            ->placeholder('Aucune observation renseignée.'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProjectsRelationManager::class,
            ApplicationsRelationManager::class,
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
