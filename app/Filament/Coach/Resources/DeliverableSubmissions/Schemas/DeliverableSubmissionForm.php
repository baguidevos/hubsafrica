<?php

namespace App\Filament\Coach\Resources\DeliverableSubmissions\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliverableSubmissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sae_id')
                    ->relationship('sae', 'name')
                    ->required(),
                Select::make('definition_id')
                    ->relationship('definition', 'name')
                    ->required(),
                Select::make('project_id')
                    ->relationship('project', 'name')
                    ->required(),
                Select::make('entrepreneur_id')
                    ->relationship('entrepreneur', 'id')
                    ->default(null),
                Select::make('submitted_by_id')
                    ->relationship('submittedBy', 'name')
                    ->default(null),
                TextInput::make('version')
                    ->required()
                    ->numeric()
                    ->default(1),
                TextInput::make('file_path')
                    ->default(null),
                TextInput::make('file_name')
                    ->default(null),
                Textarea::make('comment')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->required()
                    ->default('SOUMIS'),
                DateTimePicker::make('submitted_at')
                    ->required(),
            ]);
    }
}
