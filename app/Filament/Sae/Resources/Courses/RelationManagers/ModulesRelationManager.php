<?php

namespace App\Filament\Sae\Resources\Courses\RelationManagers;

use App\Filament\Sae\Resources\CourseModules\CourseModuleResource;
use App\Models\CourseModule;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ModulesRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    protected static ?string $title = 'Modules du cours';

    protected static ?string $modelLabel = 'Module';

    protected static ?string $pluralModelLabel = 'Modules';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Détails du module')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->columnSpanFull()
                            ->schema([
                                TextInput::make('title')
                                    ->label('Titre du module')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                TextInput::make('sort_order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                Toggle::make('is_free')
                                    ->label('Aperçu libre (tous paliers)')
                                    ->inline(false)
                                    ->default(false),

                                Toggle::make('is_published')
                                    ->label('Publié')
                                    ->inline(false)
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->label('Module')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('lessons_count')
                    ->label('Leçons')
                    ->counts('lessons')
                    ->badge()
                    ->color('info'),

                IconColumn::make('is_free')
                    ->label('Aperçu libre')
                    ->boolean(),

                IconColumn::make('is_published')
                    ->label('Publié')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter un module')
                    ->modalWidth(Width::ThreeExtraLarge),
            ])
            ->recordActions([
                Action::make('manage_lessons')
                    ->label('Gérer les leçons')
                    ->icon('heroicon-o-film')
                    ->color('primary')
                    ->url(fn (CourseModule $record): string => CourseModuleResource::getUrl('edit', ['record' => $record])),
                EditAction::make()->modalWidth(Width::ThreeExtraLarge),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order');
    }
}
