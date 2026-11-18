<?php

namespace App\Filament\Sae\Resources\CourseModules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CourseModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations du module')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->columnSpanFull()
                            ->schema([
                                Select::make('course_id')
                                    ->label('Cours parent')
                                    ->relationship('course', 'title')
                                    ->required()
                                    ->searchable()
                                    ->columnSpan(['default' => 1, 'md' => 2]),

                                TextInput::make('sort_order')
                                    ->label('Ordre')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0),

                                TextInput::make('title')
                                    ->label('Titre du module')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Textarea::make('description')
                                    ->label('Description')
                                    ->rows(3)
                                    ->columnSpanFull(),

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
}
