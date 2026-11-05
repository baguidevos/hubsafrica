<?php

namespace App\Filament\Admin\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Password;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required(),
                TextInput::make('email')
                    ->label('Adresse email')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at')
                    ->label('Email vérifié le'),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->rule(Password::default())
                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Laissez vide pour conserver le mot de passe actuel.' : null),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->default(null),
                TextInput::make('avatar_url')
                    ->label('URL de l\'avatar')
                    ->url()
                    ->default(null),
                TextInput::make('status')
                    ->label('Statut')
                    ->required()
                    ->default('ACTIVE'),
            ]);
    }
}
