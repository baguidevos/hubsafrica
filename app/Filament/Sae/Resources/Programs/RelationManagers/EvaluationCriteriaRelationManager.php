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

class EvaluationCriteriaRelationManager extends RelationManager
{
    protected static string $relationship = 'evaluationCriteria';

    protected static ?string $title = 'Grille de sélection & Critères Jury';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-clipboard-document-check';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    TextInput::make('name')
                        ->label('Critère d\'évaluation')
                        ->placeholder('Ex: Qualité de l\'équipe fondatrice')
                        ->required()
                        ->columnSpan(2),

                    TextInput::make('position')
                        ->label('Ordre')
                        ->numeric()
                        ->default(1)
                        ->required(),
                ]),

                Grid::make(2)->schema([
                    TextInput::make('weight')
                        ->label('Coefficient / Pondération')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->maxValue(10)
                        ->helperText('Ex: 1 = normal, 2 = prioritaire, 3 = éliminatoire / stratégique')
                        ->required(),

                    TextInput::make('max_score')
                        ->label('Note maximale barème')
                        ->numeric()
                        ->default(10)
                        ->suffix('points')
                        ->helperText('Barème sur lequel le jury notera ce critère (ex: sur 10)')
                        ->required(),
                ]),

                Textarea::make('description')
                    ->label('Consignes & guide de notation pour le jury')
                    ->placeholder('Précisez les points d\'attention : complémentarité, expérience, réactivité...')
                    ->rows(2)
                    ->columnSpanFull()
                    ->helperText('Ce texte sera affiché au membre du jury lors de sa notation'),
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
                    ->label('Critère')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('weight')
                    ->label('Coefficient')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "Poids {$state}x")
                    ->alignCenter(),

                TextColumn::make('max_score')
                    ->label('Barème')
                    ->suffix(' pts')
                    ->alignCenter(),

                TextColumn::make('description')
                    ->label('Guide de notation')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->description)
                    ->default('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nouveau critère')
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('position', 'asc');
    }
}
