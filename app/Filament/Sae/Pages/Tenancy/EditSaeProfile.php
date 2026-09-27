<?php

namespace App\Filament\Sae\Pages\Tenancy;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Schemas\Schema;

class EditSaeProfile extends EditTenantProfile
{
    public static function getLabel(): string
    {
        return 'Profil de la SAE';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom de la structure')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email de contact')
                    ->email(),

                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel(),

                Textarea::make('address')
                    ->label('Adresse')
                    ->rows(2),

                Textarea::make('description')
                    ->label('Description & Mission')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}


