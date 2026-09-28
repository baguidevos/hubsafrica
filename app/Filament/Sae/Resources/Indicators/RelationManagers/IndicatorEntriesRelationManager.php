<?php

namespace App\Filament\Sae\Resources\Indicators\RelationManagers;

use App\Models\Project;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndicatorEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'entries';

    protected static ?string $title = 'Mesures périodiques & Relevés d\'impact';

    protected static string|\BackedEnum|null $icon = 'heroicon-o-chart-bar';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)->schema([
                    Select::make('project_id')
                        ->label('Startup / Projet concerné')
                        ->options(Project::all()->pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    DatePicker::make('period_on')
                        ->label('Date ou premier jour du mois mesuré')
                        ->default(now()->startOfMonth())
                        ->required(),

                    TextInput::make('value')
                        ->label('Valeur observée / réalisée')
                        ->numeric()
                        ->required()
                        ->helperText('Unité selon l\'indicateur (FCFA, nombre, %)'),
                ]),

                Textarea::make('comment')
                    ->label('Commentaires, sources & éléments probants')
                    ->placeholder('Ex: D\'après la déclaration TVA ou le grand livre comptable...')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('project.name')
                    ->label('Startup')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('period_on')
                    ->label('Période')
                    ->date('m/Y')
                    ->sortable(),

                TextColumn::make('value')
                    ->label('Valeur')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: ' ')
                    ->weight('bold')
                    ->color('success')
                    ->alignRight()
                    ->sortable(),

                TextColumn::make('comment')
                    ->label('Notes')
                    ->limit(40)
                    ->default('—'),

                TextColumn::make('created_at')
                    ->label('Saisi le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Enregistrer une mesure')
                    ->slideOver(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->defaultSort('period_on', 'desc');
    }
}
