<?php

namespace App\Filament\Sae\Pages\Auth;

use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class RegisterSaeUser extends BaseRegister
{
    public function getHeading(): string|Htmlable|null
    {
        return 'Créer un compte Responsable SAE';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Inscription Structure d\'Accompagnement (SAE)';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                TextInput::make('phone')
                    ->label('Numéro de téléphone / WhatsApp')
                    ->tel()
                    ->maxLength(30),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }
}
