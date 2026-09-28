<?php

namespace App\Filament\Sae\Resources\AuditLogs\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AuditLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('sae_id')
                    ->relationship('sae', 'name')
                    ->default(null),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                TextInput::make('user_name')
                    ->default(null),
                TextInput::make('action')
                    ->required(),
                TextInput::make('entity')
                    ->required(),
                TextInput::make('entity_id')
                    ->default(null),
                Textarea::make('details')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('ip_address')
                    ->default(null),
            ]);
    }
}
