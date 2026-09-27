<?php

namespace App\Filament\Sae\Resources\Coaches\Schemas;

use App\Enums\CoachStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CoachForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité & Profil du Coach')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->label('Nom complet')
                                ->placeholder('Ex: Marie Sarr')
                                ->required(),

                            TextInput::make('email')
                                ->label('Adresse email')
                                ->email()
                                ->placeholder('marie.sarr@coach.sn')
                                ->required(),

                            TextInput::make('phone')
                                ->label('Téléphone')
                                ->tel()
                                ->placeholder('+221 77 123 45 67'),

                            TextInput::make('availability')
                                ->label('Disponibilités')
                                ->placeholder('Ex: 4h / semaine, Mercredi & Vendredi'),

                            TextInput::make('max_capacity')
                                ->label('Capacité maximale d\'accompagnement')
                                ->numeric()
                                ->default(10)
                                ->suffix('startups')
                                ->required(),

                            Select::make('status')
                                ->label('Statut du coach')
                                ->options(CoachStatus::class)
                                ->default(CoachStatus::ACTIF)
                                ->required(),

                            Toggle::make('is_external')
                                ->label('Coach externe / Consultant indépendant')
                                ->default(true)
                                ->inline(false),

                            Select::make('user_id')
                                ->label('Compte utilisateur lié (optionnel)')
                                ->relationship('user', 'name')
                                ->searchable()
                                ->preload(),
                        ]),

                        TagsInput::make('specialties')
                            ->label('Expertises / Domaines d\'intervention')
                            ->placeholder('Ajouter une spécialité (ex: Pitch, Business Plan, Levée de fonds, Juridique)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
