<?php

namespace App\Filament\Sae\Resources\Programs\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PhasesRelationManager extends RelationManager
{
    protected static string $relationship = 'phases';

    protected static ?string $title = 'Phases & Jalons pédagogiques';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-flag';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->label('Nom du jalon / de la phase')
                        ->placeholder('Ex: Phase 1 : Diagnostic & Boot Camp')
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('position')
                        ->label('Ordre')
                        ->numeric()
                        ->default(1)
                        ->helperText('Position chronologique')
                        ->required(),
                ]),

                TextInput::make('duration_weeks')
                    ->label('Durée estimée')
                    ->numeric()
                    ->suffix('semaines')
                    ->helperText('Permet de projeter le calendrier lors de la création d\'une cohorte'),

                Textarea::make('description')
                    ->label('Description du contenu')
                    ->placeholder('Présentation des modules, ateliers et accompagnements prévus...')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('objectives')
                    ->label('Objectifs pédagogiques & livrables attendus')
                    ->placeholder('Ex: Valider le MVP, structurer la proposition de valeur...')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('passing_criteria')
                    ->label('Critères de passage au jalon suivant')
                    ->placeholder('Ex: Participation à 80% des ateliers, dépôt du Business Plan validé...')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Indispensable pour l\'évaluation de fin de phase par le comité'),
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
                    ->label('Nom de la phase')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('duration_weeks')
                    ->label('Durée')
                    ->suffix(' sem.')
                    ->default('—')
                    ->alignCenter(),

                TextColumn::make('passing_criteria')
                    ->label('Critère de passage')
                    ->limit(45)
                    ->tooltip(fn ($record) => $record->passing_criteria)
                    ->default('Non défini'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un jalon')
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('position', 'asc');
    }
}
