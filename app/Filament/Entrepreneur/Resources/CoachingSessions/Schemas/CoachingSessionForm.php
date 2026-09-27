<?php

namespace App\Filament\Entrepreneur\Resources\CoachingSessions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoachingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sae_id')
                    ->relationship('sae', 'name')
                    ->required(),
                Select::make('coach_id')
                    ->relationship('coach', 'name')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->default(null),
                Select::make('entrepreneur_id')
                    ->relationship('entrepreneur', 'id')
                    ->default(null),
                DateTimePicker::make('date')
                    ->required(),
                TextInput::make('duration')
                    ->required()
                    ->numeric()
                    ->default(60),
                TextInput::make('type')
                    ->required(),
                TextInput::make('theme')
                    ->default(null),
                Textarea::make('report')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('objectives')
                    ->default(null)
                    ->columnSpanFull(),
                Textarea::make('next_actions')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('PLANIFIEE'),
            ]);
    }
}


